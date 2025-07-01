<?php

declare(strict_types=1);

namespace Vix\Syntra\Commands\Extension\Yii;

use Vix\Syntra\Commands\SyntraCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Vix\Syntra\Utils\FileHelper;

class YiiCheckTranslationsCommand extends SyntraCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('yii:check-translations')
            ->setDescription('Checks Yii::t translations: finds missing and unused keys across all categories.')
            ->setHelp('')
            ->addOption('lang', null, InputOption::VALUE_OPTIONAL, 'Language to check (default: all found)', null);
    }

    public function perform(): int
    {
        $projectRoot = $this->configLoader->getProjectRoot();

        $messagesDir = "$projectRoot/backend/messages";
        if (!is_dir($messagesDir)) {
            $this->output->error('Translations directory not found.');
            return Command::FAILURE;
        }

        $used = $this->findUsedTranslationKeys($projectRoot);
        $found = $this->findExistingTranslationKeys($messagesDir, $this->input->getOption('lang'));

        $missing = [];
        $unused = [];

        foreach ($used as $cat => $catKeys) {
            foreach ($catKeys as $msg) {
                if (empty($found[$cat]) || !array_key_exists($msg, $found[$cat])) {
                    $missing[] = [$cat, $msg];
                }
            }
        }

        foreach ($found as $cat => $catKeys) {
            foreach (array_keys($catKeys) as $msg) {
                if (empty($used[$cat]) || !in_array($msg, $used[$cat], true)) {
                    $unused[] = [$cat, $msg];
                }
            }
        }

        if (!$missing && !$unused) {
            $this->output->success('All translations are in sync. 👍');
            return Command::SUCCESS;
        }

        if ($missing) {
            $this->output->warning('Missing translations:');
            $this->table(['Category', 'Key'], $missing);
        }

        if ($unused) {
            $this->output->info('Unused translations:');
            $this->table(['Category', 'Key'], $unused);
        }

        return Command::FAILURE;
    }

    /**
     * Find all Yii::t('category', 'message') used in the project.
     *
     * @return array<string, string[]>  category => [key, ...]
     */
    private function findUsedTranslationKeys(string $root, ?string $filterCategory = null): array
    {
        $fileHelper = new FileHelper();
        $files = $fileHelper->collectFiles($root);
        $used = [];

        $pattern = '/Yii::t\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]/';

        foreach ($files as $filePath) {
            $content = @file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            if (preg_match_all($pattern, $content, $m, PREG_SET_ORDER)) {
                foreach ($m as $match) {
                    [$_, $cat, $msg] = $match;
                    if ($filterCategory && $cat !== $filterCategory) {
                        continue;
                    }

                    $used[$cat][] = $msg;
                }
            }
        }

        foreach ($used as &$arr) {
            $arr = array_unique($arr);
        }

        return $used;
    }

    /**
     * Search all existing translation keys in messages
     *
     * @return array<string, array<string, string>> category => [key => translation]
     */
    private function findExistingTranslationKeys(string $messagesDir, ?string $lang = null, ?string $filterCategory = null): array
    {
        $result = [];

        $langs = [];
        if ($lang) {
            $langs[] = $lang;
        } else {
            foreach (scandir($messagesDir) as $item) {
                if ($item[0] !== '.' && is_dir("$messagesDir/$item")) {
                    $langs[] = $item;
                }
            }
        }

        foreach ($langs as $lng) {
            $dir = "$messagesDir/$lng";
            if (!is_dir($dir)) {
                continue;
            }

            foreach (scandir($dir) as $file) {
                if ($file[0] === '.' || !str_ends_with($file, '.php')) {
                    continue;
                }

                $category = basename($file, '.php');
                if ($filterCategory && $category !== $filterCategory) {
                    continue;
                }

                $arr = @include "$dir/$file";
                if (is_array($arr)) {
                    foreach ($arr as $key => $value) {
                        $result[$category][$key] = $value;
                    }
                }
            }
        }

        return $result;
    }
}
