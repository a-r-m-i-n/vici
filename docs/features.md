# Features

- Create new data structures (tables and table columns) directly in the TYPO3 backend
- Easy and intuitive interface
- No admin privileges are required
- No access to the code base is required
- All common TCA types and properties are supported
- New translatable field type allows you to define translations for available backend languages (used for eg. labels, descriptions or placeholder texts)
- Built in TCA and Extbase proxy class generator (applies after the system cache has been flushed)
- Native usage of TYPO3 Extbase features in the frontend (like translation handling)

## VICI backend module

- Shows an overview of all created VICI tables within the whole page tree
- Displays the status of VICI tables and informs you if changes are not applied yet, because of TCA cache
- Allows you to perform database structure updates for single VICI tables, if necessary

## VICI frontend plugin

- Allows you to output the new data structures in frontend
- Define sys folders (and recursive depth) where your records, based on VICI tables, are located
- Inline code editor for Fluid template, to define the HTML output
- Optional pagination can get enabled
- Optional detail pages can get enabled (providing a second inline Fluid template)
- Automatic registration of route enhancers (for pagination and detail pages)
- Automatic registration of XML sitemap, for detail pages
