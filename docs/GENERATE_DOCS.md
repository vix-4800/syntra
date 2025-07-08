# GenerateDocs Command

The `general:generate-docs` command scans project controllers to list all action routes in a markdown file.

In addition, the command searches controller and view files to count how many times each route is referenced. These counts are displayed in the CLI output, included in a `Refs` column in `docs/routes.md`, and the total number of references is shown in the final success message.

Run the command from your project root:

```bash
vendor/bin/syntra general:generate-docs
```

The generated documentation is written to `docs/routes.md`.
