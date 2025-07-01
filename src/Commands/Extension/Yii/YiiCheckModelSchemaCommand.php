<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Throwable;
use Vix\Syntra\Utils\FileHelper;
use Yii;
use yii\db\ActiveRecord;

class YiiCheckModelSchemaCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:check-model-schema')
            ->setDescription('Compares AR models with DB schema: finds extra or missing attributes.')
            ->setHelp('')
            ->addOption('rules', null, InputOption::VALUE_NONE, 'Compare attributes with those listed in rules() instead of all DB columns.');
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();
        $modelsDir = "$projectRoot/backend/models";

        $fileHelper = new FileHelper();
        $files = $fileHelper->collectFiles($modelsDir, ['php']);

        $results = [];

        foreach ($files as $file) {
            $class = $this->getClassFromFile($file);
            if (!$class) {
                continue;
            }
            if (!is_subclass_of($class, ActiveRecord::class)) {
                continue;
            }

            // Instantiate model
            try {
                $model = Yii::createObject($class);
            } catch (Throwable) {
                continue; // skip broken/abstract/etc.
            }

            // 1. Table columns from DB
            try {
                $tableSchema = $model::getTableSchema();
            } catch (Throwable) {
                $results[] = [$class, 'ERROR: cannot get table schema'];
                continue;
            }
            $dbColumns = array_keys($tableSchema->columns);

            // 2. Attributes from model (rules or all attributes)
            $useRules = $this->input->getOption('rules');
            $attrFromModel = $useRules
                ? $this->extractAttributesFromRules($model)
                : $model->attributes();

            // 3. Compare
            $missing = array_diff($dbColumns, $attrFromModel);
            $extra = array_diff($attrFromModel, $dbColumns);

            if ($missing || $extra) {
                $msg = [];

                if ($missing) {
                    $msg[] = "Missing: " . implode(', ', $missing);
                }

                if ($extra) {
                    $msg[] = "Extra: " . implode(', ', $extra);
                }

                $results[] = [$class, implode('; ', $msg)];
            }
        }

        if (!$results) {
            $this->output->success("All AR models are in sync with DB schema! 👍");
            return Command::SUCCESS;
        }

        $this->output->warning("Model/DB schema differences found:");
        $this->table(['Model', 'Schema Issues'], $results);

        return Command::FAILURE;
    }

    /**
     * Gets the class name from a file
     */
    private function getClassFromFile(string $file): ?string
    {
        $src = file_get_contents($file);

        return !preg_match('/namespace\s+([^\s;]+);/', $src, $ns) || !preg_match('/class\s+([^\s{]+)/', $src, $cl)
            ? null
            : "$ns[1]\\$cl[1]";
    }

    /**
     * Finds attributes from rules(), eg: [['field1', 'field2'], 'string']
     */
    private function extractAttributesFromRules($model): array
    {
        $attrs = [];
        $rules = $model->rules();

        foreach ($rules as $rule) {
            if (is_array($rule[0])) {
                $attrs = array_merge($attrs, $rule[0]);
            } else {
                $attrs[] = $rule[0];
            }
        }

        return array_unique($attrs);
    }
}
