# Markdown Tables

## Simple Table

| Name       | Age | City         |
|------------|-----|--------------|
| John Doe   | 29  | New York     |
| Jane Smith | 34  | San Francisco|
| Sam Green  | 22  | Boston       |

## Table 1

| Method name | Description | Parameters | Default |
| ----------- | ----------- | ---------- | ---------- |
| `setIcon` | icon file, or existing icon identifier | `string $icon` | `'EXT:container/Resources/Public/Icons/Extension.svg'` |
| `setBackendTemplate` | Template for backend view| `string $backendTemplate` | `null'` |
| `setGridTemplate` | Template for grid | `string $gridTemplate` | `'EXT:container/Resources/Private/Templates/Container.html'` |
| `setGridPartialPaths` / `addGridPartialPath` | Partial root paths for grid | `array $gridPartialPaths` / `string $gridPartialPath` | `['EXT:backend/Resources/Private/Partials/', 'EXT:container/Resources/Private/Partials/']` |
| `setGridLayoutPaths` | Layout root paths for grid | `array $gridLayoutPaths` | `[]` |
| `setSaveAndCloseInNewContentElementWizard` | saveAndClose for new content element wizard | `bool $saveAndCloseInNewContentElementWizard` | `true` |
| `setRegisterInNewContentElementWizard` |register in new content element wizard | `bool $registerInNewContentElementWizard` | `true` |
| `setGroup` | Custom Group (used as optgroup for CType select, and as tab in New Content Element Wizard). If empty "container" is used as tab and no optgroup in CType is used. | `string $group` | `'container'` |
| `setDefaultValues` | Default values for the newContentElement.wizardItems | `array $defaultValues` | `[]` |

## Table 2

| Option                      | Description                                                                                                | Default                                                      | Parameter   |
|-----------------------------|------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------|-------------|
| `contentId`                 | id of container to to process                                                                              | current uid of content element ``$cObj->data['uid']``        | ``?int``    |
| `colPos`                    | colPos of children to to process                                                                           | empty, all children are processed (as ``children_<colPos>``) | ``?int``    |
| `as`                        | variable to use for proceesedData (only if ``colPos`` is set)                                              | ``children``                                                 | ``?string`` |
| `skipRenderingChildContent` | do not call ``ContentObjectRenderer->render()`` for children, (``renderedContent`` in child will not exist) | empty                                                        | ``?int``    |
