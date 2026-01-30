Module is installed in a regular way - simply upload your archive and click install.

CHANGELOG:
===========================
v 3.3.0 (December 28, 2024)
===========================
- [*] Improved compatibility with SEO Pages module v1.0.1
- [*] Improved compatibility with PrestaShop Edition Basic UI
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/bo.php
- /upgrade/install-3.3.0.php
- /src/AmazzingFilterProductSearchProvider.php
- /views/css/back.css
- /views/css/cf-back.css
- /views/js/cf.js
- /views/js/front.js
- /views/templates/admin/available-filters.tpl
- /views/templates/admin/configure.tpl
- /views/templates/admin/input.tpl
- /views/templates/admin/template-form.tpl
- /views/templates/admin/template-group.tpl

Files added:
-----
- /upgrade/install-3.3.0.php
- /views/css/ps-edition-basic.css

===========================
v 3.2.9 (July 23, 2024)
===========================
- [*] Compatibility with SEO Pages module v1.0.0
- [*] Improved compatibility with statssearch module
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/bo.php
- /classes/CustomerFilters.php
- /classes/MergedValues.php
- /views/css/back.css
- /views/js/back.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/pagination.tpl
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/cf-form.tpl

===========================
v 3.2.8 (May 1, 2024)
===========================
- [+] Support negative values in numeric attribute/feature sliders
- [*] Improved performance of numeric attribute/feature sliders
- [*] Fixed minor CLS issues caused by dynamic elements in numeric sliders
- [*] Removed the 'unload' event based on Google PSI recommendations
- [*] Slightly updated Polish translations
- [*] Misc bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/AfSlider.php
- /translations/pl.php
- /upgrade/install-3.2.7.php
- /views/css/slider.css
- /views/js/back.js
- /views/js/front.js
- /views/js/slider.js
- /views/templates/admin/configure.tpl

Files added:
-----
- /classes/RangeFilter.php
- /classes/Toolkit.php
- /upgrade/install-3.2.8.php

Files removed:
-----
- /classes/ExtendedTools.php

===========================
v 3.2.7 (February 1, 2024)
===========================
- [+] Possibility to merge attributes/features from different groups
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/bo.php
- /classes/MergedValues.php
- /upgrade/install-3.2.6.php
- /views/css/back.css
- /views/css/front.css
- /views/js/back.js
- /views/js/cf-back.js
- /views/js/front.js
- /views/templates/admin/input.tpl
- /views/templates/admin/merged-items.tpl

Files added:
-----
- /upgrade/install-3.2.7.php
- /views/templates/admin/merged-qs.tpl

===========================
v 3.2.6 (December 5, 2023)
===========================
- [+] Button for resetting predefined customer filters
- [*] Security improvements
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/CustomerFilters.php
- /classes/MergedValues.php
- /classes/RelatedOverrides.php
- /controllers/front/ajax.php
- /upgrade/install-3.2.5.php
- /views/css/cf.css
- /views/css/front.css
- /views/js/back.js
- /views/js/cf.js
- /views/js/front.js
- /views/js/specific/alysum-17.js
- /views/templates/admin/cf-configure.tpl
- /views/templates/admin/configure.tpl
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/cf-form.tpl

Files added:
-----
- /.htaccess
- /upgrade/install-3.2.6.php

===========================
v 3.2.5 (August 18, 2023)
===========================
- [*] Significantly improved customer filters: new interface, new options, etc.
- [*] Fixed incorrect numbers of matches in some complex multishop scenarios
- [*] Improved compatibility with Alysum theme
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/bo.php
- /controllers/front/ajax.php
- /readme_en.pdf
- /src/AmazzingFilterProductSearchProvider.php
- /upgrade/install-3.0.0.php
- /upgrade/install-3.0.3.php
- /upgrade/install-3.1.9.php
- /upgrade/install-3.2.3.php
- /views/css/back.css
- /views/css/front.css
- /views/css/specific/default-bootstrap-16.css
- /views/css/specific/warehouse-17.css
- /views/js/back.js
- /views/js/front.js
- /views/js/slider.js
- /views/js/specific/alysum-17.js
- /views/templates/admin/available-filters.tpl
- /views/templates/admin/configure.tpl
- /views/templates/admin/form-group.tpl
- /views/templates/admin/hook-positions-form.tpl
- /views/templates/admin/input.tpl
- /views/templates/admin/template-form.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /classes/CustomerFilters.php
- /upgrade/install-3.2.5.php
- /views/css/cf-back.css
- /views/css/cf.css
- /views/js/cf-back.js
- /views/js/cf.js
- /views/templates/admin/cf-configure.tpl
- /views/templates/hook/cf-block.tpl
- /views/templates/hook/cf-form.tpl

Files removed:
-----
- /controllers/front/myfilters.php
- /views/css/my-filters.css
- /views/js/my-filters.js
- /views/templates/front/content-17.tpl
- /views/templates/front/my-filters.tpl
- /views/templates/hook/my-filters-tab.tpl

===========================
v 3.2.4 (May 22, 2023)
===========================
- [+] New sorting option: Recently updated
- [*] Updated some French translations
- [*] Improved infinite scrolling behavior in specific scenarios
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/bo.php
- /classes/MergedValues.php
- /translations/fr.php
- /upgrade/install-3.1.9.php
- /views/css/back.css
- /views/css/front.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/available-filters.tpl

===========================
v 3.2.3 (March 12, 2023)
===========================
- [+] Configurable MORE FILTERS button
- [+] New sorting option: By weight
- [+] Possibility to display filter in hook displayHeaderCategory
- [+] Romanian translation (thanks to Daniel Merior)
- [*] Improved back office performance in specific scenarios
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/MergedValues.php
- /readme_en.pdf
- /upgrade/install-3.2.0.php
- /views/css/back.css
- /views/css/front.css
- /views/css/slider.css
- /views/css/specific/classic-17.css
- /views/js/back.js
- /views/js/front-16.js
- /views/js/front.js
- /views/js/specific/warehouse-17.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/merged-items.tpl
- /views/templates/admin/options.tpl
- /views/templates/admin/template-form.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /translations/ro.php
- /upgrade/install-3.2.3.php

===========================
v 3.2.2 (January 3, 2023)
===========================
- [*] Compatibility with PS 8.0
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/AfSlider.php
- /classes/RelatedOverrides.php
- /classes/bo.php
- /controllers/front/myfilters.php
- /readme_en.pdf
- /upgrade/install-2.6.0.php
- /upgrade/install-2.7.0.php
- /upgrade/install-2.8.2.php
- /upgrade/install-3.2.0.php
- /views/css/back.css
- /views/css/front.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/options.tpl
- /views/templates/admin/template-form.tpl
- /views/templates/hook/dynamic-loading.tpl
- /views/templates/hook/my-filters-tab.tpl

Files removed:
-----
- /views/css/back-17.css

===========================
v 3.2.1 (November 2, 2022)
===========================
- [+] Quick search for Categories/Manufacturers/Suppliers in template settings
- [*] Improved indexation of discounted prices in some complex scenarios
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/back.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/options.tpl

===========================
v 3.2.0 (September 27, 2022)
===========================
- [+] Admin interface for editing custom CSS/JS
- [+] Optionally sort filtering options by number of matching products
- [*] Fixed incorrectly displayed slider values in some specific scenarios
- [*] Removed irrelevant headers h2, h5 from filter block
- [*] Improved compatibility with some themes: akira, at_classico, at_decor, at_movic, at_oreo
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/bo.php
- /views/css/back.css
- /views/css/front.css
- /views/js/back.js
- /views/js/front.js
- /views/js/slider.js
- /views/js/specific/akira-17.js
- /views/js/specific/at_classico-17.js
- /views/js/specific/at_decor-17.js
- /views/js/specific/at_movic-17.js
- /views/js/specific/at_oreo-17.js
- /views/js/specific/panda-17.js
- /views/templates/admin/configure.tpl
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/my-filters-tab.tpl

Files added:
-----
- /upgrade/install-3.2.0.php
- /views/css/front-16.css
- /views/css/specific/default-bootstrap-16.css

Files removed:
-----
- /views/css/custom.css
- /views/css/front-17.css

===========================
v 3.1.9 (June 12, 2022)
===========================
- [+] Configurable sorting options in PS 1.7+
- [+] Configurable number of days for bestsellers and new products
- [+] French translation (thanks to Marc Fournier)
- [*] Display price/image of discounted combinations when "prices drop" filter is applied
- [*] Improved compatibility with Akira theme in PS 1.7
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/MergedValues.php
- /translations/it.php
- /views/css/back.css
- /views/css/front.css
- /views/css/rtl.css
- /views/css/specific/warehouse-17.css
- /views/js/back.js
- /views/js/front.js
- /views/js/specific/warehouse-17.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/form-group.tpl
- /views/templates/admin/input.tpl
- /views/templates/admin/template-group.tpl
- /views/templates/hook/amazzingfilter.tpl
- upgrade-files for: v2.1.2, v2.5.3, v2.6.0, v2.7.0, v2.8.2

Files added:
-----
- /classes/RelatedOverrides.php
- /override_files/17/classes/Product.php
- /translations/fr.php
- /upgrade/install-3.1.9.php
- /views/js/specific/akira-17.js

Files moved:
- all files from /override_files/ to /override_files/16/
- all files from /indexes/ to /data/

===========================
v 3.1.8 (February 4, 2022)
===========================
- [+] New filters: online only, on sale!
- [+] Dynamically update min/max for numeric attribute/feature sliders after filtration
- [*] Fixed pagination on SEO Pages in Transformer theme in PS 1.7
- [*] Improved compatibility with MODEZ theme in PS 1.7
- [*] Improved compatibility with Alysum theme in PS 1.7
- [*] Improved compatibility with Z.One theme in PS 1.7
- [*] Improved compatibility with PS 1.7.8
- [*] Improved compatibility with PHP 7.4
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/AfSlider.php
- /classes/bo.php
- /controllers/front/ajax.php
- /views/css/back.css
- /views/css/specific/modez-17.css
- /views/css/specific/warehouse-17.css
- /views/js/back.js
- /views/js/front-16.js
- /views/js/front.js
- /views/js/specific/classic-17.js
- /views/js/specific/modez-17.js
- /views/js/specific/transformer-17.js
- /views/templates/admin/input.tpl
- /views/templates/admin/template-form.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /upgrade/install-3.1.8.php
- /views/js/specific/alysum-17.js

===========================
v 3.1.7 (September 7, 2021)
===========================
- [+] Dynamically update min/max values of price slider after filtration
- [+] Optionally index individual prices for product combinations
- [+] Optionally index product associations only with active categories
- [*] Improved filtering time on large catalogues
- [*] Updated Polish translation
- [*] Fixed dynamic pagination in Warehouse theme in PS 1.7
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/ajax.php
- /src/AmazzingFilterProductSearchProvider.php
- /translations/pl.php
- /views/css/front.css
- /views/js/front.js
- /views/js/specific/warehouse-17.js
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /upgrade/install-3.1.7.php

===========================
v 3.1.6 (July 19, 2021)
===========================
- [+] Configurable join type AND/OR for multiple options selected in group
- [*] Fixed incorrect numbers of matches in some specific scenarios
- [*] Included group discounts in price indexation data
- [*] Improved auto-indexation on saving products in PS 1.7
- [*] Improved compatibility with Z.One theme in PS 1.7
- [*] Improved compatibility with Warehouse theme in PS 1.7
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/AfSlider.php
- /views/css/specific/warehouse-17.css
- /views/js/front.js
- /views/js/specific/warehouse-17.js
- /views/js/specific/ZOneTheme-17.js
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /views/js/product-indexer.js

Files removed:
-----
- /views/js/attribute-indexer.js

===========================
v 3.1.5 (March 31, 2021)
===========================
- [+] New configuration options for out-of-stock products
- [+] Italian translation
- [*] Improved RTL layout
- [*] Improved compatibility with leo-themes in PS 1.7
- [*] Improved compatibility with Transformer/Panda themes in PS 1.6/1.7
- [*] Improved compatibility with Z.One theme in PS 1.7
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/back.css
- /views/js/back.js
- /views/js/front.js
- /views/js/slider.js
- /views/js/specific/at_classico-17.js
- /views/js/specific/at_decor-17.js
- /views/js/specific/at_movic-17.js
- /views/js/specific/at_oreo-17.js
- /views/js/specific/panda-17.js
- /views/js/specific/transformer-16.js
- /views/js/specific/transformer-17.js
- /views/js/specific/ZOneTheme-17.js
- /views/templates/admin/form-group.tpl

Files added:
-----
- /classes/AfSlider.php
- /translations/it.php
- /views/css/rtl.css
- /views/js/rtl-slider.js

Files removed:
-----
- /classes/Slider.php

===========================
v 3.1.4 (December 24, 2020)
===========================
- [*] Fixed saving settings in PS 1.7.7
- [*] Fixed combination images in PS 1.7.7
- [*] Fixed some specific scenarios in price slider
- [*] Fixed escaping special chars in some JS variables
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/js/front.js
- /views/js/slider.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/hook-positions-form.tpl

===========================
v 3.1.3 (October 28, 2020)
===========================
- [+] Optionally display color boxes with/without names
- [+] Optionally define max. visible items for each filtering block
- [+] New option for instantly saving settings in BO
- [*] Improved support for decimal values in numeric sliders
- [*] Slightly updated design for filter block
- [*] Fixed non-available supplier filter on template for search results page
- [*] Improved compatibility with Classic Rocket theme in PS 1.7
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /classes/bo.php
- /classes/Slider.php
- /translations/pl.php
- /views/css/back.css
- /views/css/front.css
- /views/css/slider.css
- /views/css/specific/warehouse-17.css
- /views/js/back.js
- /views/js/front.js
- /views/js/slider.js
- /views/js/specific/panda-17.js
- /views/js/specific/transformer-16.js
- /views/js/specific/transformer-17.js
- /views/js/specific/warehouse-17.js
- /views/templates/admin/available-filters.tpl
- /views/templates/admin/configure.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /classes/ExtendedTools.php
- /upgrade/install-3.1.3.php
- /views/js/front-16.js
- /views/js/specific/classic-rocket-17.js
- /views/templates/admin/errors.tpl

===========================
v 3.1.2 (September 2, 2020)
===========================
- [+] Configurable positions for merged attributes
- [*] Improved performance for stores having thousands of feature values
- [*] Improved compatibility with Z.One theme in PS 1.7
- [*] Improved compatibility with Warehouse theme in PS 1.7 and PS 1.6
- [*] Improved compatibility with At Oreo theme in PS 1.7
- [*] Improved compatibility with Transformer theme in PS 1.6
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/myfilters.php
- /translations/pl.php
- /translations/ru.php
- /views/css/back.css
- /views/css/front-17.css
- /views/css/front.css
- /views/css/specific/warehouse-17.css
- /views/js/back.js
- /views/js/front.js
- /views/js/specific/transformer-16.js
- /views/js/specific/warehouse-17.js
- /views/js/specific/ZOneTheme-17.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/merged-items.tpl
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/dynamic-loading.tpl

Files added:
-----
- /classes/MergedValues.php
- /classes/Slider.php
- /upgrade/install-3.1.2.php
- /views/js/specific/at_oreo-17.js

===========================
v 3.1.1 (June 30, 2020)
===========================
- [*] Integrated support for displaying filter block in center_column on Warehouse theme
- [*] Updated Polish and Russian translations
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/ajax.php
- /controllers/front/cron.php
- /controllers/front/myfilters.php
- /translations/pl.php
- /translations/ru.php
- /views/css/back.css
- /views/css/front.css
- /views/css/specific/warehouse-17.css
- /views/js/front.js
- /views/js/specific/warehouse-17.js
- /views/templates/admin/template-form.tpl
- /views/templates/hook/dynamic-loading.tpl

===========================
v 3.1.0 (May 11, 2020)
===========================
- [+] Configurable slider step value
- [+] Compatibility with module Custom SEO Pages (beta version available upon request)
- [*] Improved compatibility with Warehouse theme
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /src/AmazzingFilterProductSearchProvider.php
- /views/css/back.css
- /views/css/front.css
- /views/css/specific/classic-17.css
- /views/js/back.js
- /views/js/front.js
- /views/js/specific/warehouse-17.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/filter-form.tpl
- /views/templates/admin/form-group.tpl
- /views/templates/admin/input.tpl
- /views/templates/admin/template-form.tpl
- /views/templates/admin/template-group.tpl
- /views/templates/front/basic-layout-17.tpl
- /views/templates/front/basic-layout.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /classes/bo.php
- /upgrade/install-3.1.0.php
- /views/templates/admin/footer-save-btn.tpl
- /views/templates/admin/pagination.tpl

===========================
v 3.0.3 (February 17, 2020)
===========================
- [+] Optional Quick Search field for available filter options
- [+] Optionally display selected filters above product list
- [+] Configure how often should be random sorting updated: hourly, daily, weekly
- [*] Improved horizontal layout of filter block
- [*] Improved compatibility with at_classico and at_decor themes on PS 1.7
- [*] Fixed taxes impact in price slider
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/front-17.css
- /views/css/front.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/dynamic-loading.tpl

Files added:
-----
- /upgrade/install-3.0.3.php
- /views/css/specific/classic-17.css
- /views/js/specific/at_classico-17.js
- /views/js/specific/at_decor-17.js

===========================
v 3.0.2 (December 10, 2019)
===========================
- [+] Check subcategories of selected parent in tree view on template settings panel
- [+] Sort filter templates by name or date added
- [*] Fixed customer group restrictions
- [*] Fixed jQuery loading on product sheet in some specific cases on PS 1.7.5
- [*] Improved compatibility with Venedor theme on PS 1.7
- [*] Minor bug fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/back.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/configure.tpl

Files added:
-----
- /views/js/specific/venedor-17.js

===========================
v 3.0.1 (November 21, 2019)
===========================
- [*] Minor fixes related to sorting and merged values

Files modified:
-----
- /amazzingfilter.php

===========================
v 3.0.0 (November 14, 2019)
===========================
- [*] New indexation system, working a lot faster. NOTE: FULL RE-INDEX IS REQUIRED AFTER UPGRADE
- [*] Improved filtering algorithm
- [+] Optionally exclude filter parameters and page number from URL
- [+] Configurable compact filter button: Icon only, Text only, Icon + Text
- [*] Improved number formats in numeric sliders
- [*] Fixed color textures for merged attributes
- [*] Improved compatibility with at_movic theme in PS 1.7
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/ajax.php
- /controllers/front/cron.php
- /controllers/front/myfilters.php
- /views/css/back.css
- /views/css/front.css
- /views/css/front-17.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/form-group.tpl
- /views/templates/admin/input.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /upgrade/install-3.0.0.php
- /views/js/specific/at_movic-17.js

===========================
v 2.8.8 (September 30, 2019)
===========================
- [+] Optionally sort filter options by first number in name
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/js/specific/ZOneTheme-17.js

===========================
v 2.8.7 (September 21, 2019)
===========================
- [+] Optionally sort category options by name/position/ID
- [*] Improved compatibility with PS 1.7.6
- [*] Improved compatibility with Angar theme in PS 1.7
- [*] Improved compatibility with Z.One theme in PS 1.7
- [*] Improved compatibility with Transformer theme in PS 1.6
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/front.css
- /views/js/front.js
- /views/js/specific/warehouse-17.js

Files added:
-----
- /upgrade/install-2.8.7.php
- /views/js/specific/transformer-16.js
- /views/js/specific/ZOneTheme-17.js

===========================
v 2.8.6 (June 24, 2019)
===========================
- [+] New sorting option: By sales
- [+] Optional caching for selected resources
- [+] Possibility to assign templates explicitly to home category in PS 1.7
- [*] Improved pagination performance in Warehouse theme PS 1.7
- [*] Improved compatibility with Panda theme in PS 1.7
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /readme_en.pdf
- /views/css/back.css
- /views/js/back.js
- /views/js/specific/warehouse-17.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/options.tpl

Files added:
-----
- /upgrade/install-2.8.6.php

===========================
v 2.8.5 (April 18, 2019)
===========================
- [+] Possibility to merge multiple attributes/features in single option with custom name
- [+] New display type for filter groups: text boxes
- [+] Cron task for reindexing selected products
- [+] Compatibility with module Elastic Jet Search
- [*] Fixed Lazy loading images after updating product list in Transformer theme PS 1.7
- [*] Don't skip dots and commas in friendly_urls, replace them with dashes
- [*] Improved automatic re-indexation on saving products with many combinations in PS 1.7
- [*] Reorganized settings interface for better user experience
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/cron.php
- /views/css/back.css
- /views/css/front.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/form-group.tpl
- /views/templates/admin/input.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /upgrade/install-2.8.5.php
- /views/js/specific/transformer-17.js

===========================
v 2.8.4 (February 12, 2019)
===========================
- [+] Compatibility with TMsearch
- [+] New settings: compact offset direction - left/right
- [*] Improved infinite scroll/load more behavior when user clicks on product and then goes back
- [*] Fixed toggling filters in compact view if desktop view is horizontal
- [*] Minor fixes

Files modified:
-----
- /amazzingfilter.php
- /override_files/controllers/admin/AdminProductsController.php
- /views/css/front-17.css
- /views/css/front.css
- /views/js/back.js
- /views/js/specific/warehouse-17.js
- /views/js/front.js
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/dynamic-loading.tpl

Files added:
-----
- /upgrade/install-2.8.4.php

===========================
v 2.8.3 (December 15, 2018)
===========================
- [+] Compatibility with IqitSearch in PS 1.7
- [+] Optional random sorting for filtered results
- [+] Polish translation
- [*] Activate compact layout at specified browser width
- [*] View filtered products instantly on main page, without clicking on button
- [*] Display combination cover images with highest positions
- [*] Hide compact filter panel by swiping to the side
- [*] URL params are ordered in the same way as displayed filter blocks
- [*] Fixed checkboxes/radioboxes in foldered categories in Warehouse theme PS 1.7
- [*] Improved structure of scripts for easier customizations
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/ajax.php
- /controllers/front/myfilters.php
- /views/css/front.css
- /views/css/my-filters.css
- /views/css/specific/warehouse-17.css
- /views/js/front.js
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /views/js/specific/classic-17.js
- /translations/pl.php
- /upgrade/install-2.8.3.php

Files removed:
-----
- /views/js/custom.js
- /views/js/main-page.js

===========================
v 2.8.2 (July 24, 2018)
===========================
- [+] New general settings: default sorting, number of products per page, filter layout (horizontal, vertical)
- [+] Customizable general settings for each template (sorting, pagination, layout, etc...)
- [+] Filter templates for selected manufacturers/suppliers
- [*] Re-index all products in live mode, without erasing whole indexation data
- [*] Fixed load more button in mobile view, PS 1.7
- [*] Dynamically update total matches container, PS 1.6
- [*] Improved compatibility with Ayon theme, PS 1.6
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /src/AmazzingFilterProductSearchProvider.php
- /controllers/front/cron.php
- /views/css/back.css
- /views/css/front.css
- /views/css/front-17.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/form-group.tpl
- /views/templates/admin/input.tpl
- /views/templates/admin/options.tpl
- /views/templates/admin/template-form.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /override_files/classes/controller/FrontController.php
- /upgrade/install-2.8.2.php

Directories added:
-----
- /override/classes/controller/

Files removed:
-----
- /views/templates/admin/cat-tree.tpl

===========================
v 2.8.1 (March 30, 2018)
===========================
- [+] Compatibility with IqitSearch
- [+] Compatibility with LeoProductSearch
- [*] New price slider script: lightweight, touch friendly, does not depend on jquery-ui
- [*] Improved indexation
- [*] Improved compatibility with Warehouse theme in PS 1.7
- [*] Improved compatibility with Panda theme in PS 1.7
- [*] Improved compatibility with child themes in PS 1.7
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/cron.php
- /views/css/front.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/available-filters.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /views/css/slider.css
- /views/css/specific/warehouse-17.css
- /views/js/slider.js
- /views/js/specific/panda-17.js
- /views/js/specific/warehouse-17.js

Files removed:
-----
- /views/js/jquery.ui.touch-punch.min.js

===========================
v 2.8.0 (December 9, 2017)
===========================
- [+] Optionally minimize selected filters on page load
- [+] Optionally include sorting parameter in URL
- [+] Optionally show/hide group names of selected filters
- [+] Editable custom names for each filter group
- [+] Custom prefixes/suffixes for numeric sliders
- [+] Configurable decimal/thousand separators for numeric values (1500.5 or 1.500,5)
- [+] Possibility to duplicate templates
- [+] Possibility to display templates on all categories without explicit selection (including new created)
- [+] Recognize ranged values in numeric sliders (1-3 years, 1-5 years, 5-8 years etc...)
- [+] Smarty variables required for displaying add to cart button in product list in 1.7
- [*] Fixed weight indexation for products without combinations
- [*] Improved compatibility with transformer theme in 1.7
- [*] Initial sorting by price:asc on pricesdrop page in 1.6
- [*] Reload page on clicking back/forward in browser after URL was updated basing on applied filters
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/myfilters.php
- /views/css/back.css
- /views/css/front.css
- /views/js/back.js
- /views/js/front.js
- /views/templates/admin/cat-tree.tpl
- /views/templates/admin/configure.tpl
- /views/templates/admin/filter-form.tpl
- /views/templates/admin/form-group.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /upgrade/install-2.8.0.php
- /views/templates/admin/available-filters.tpl
- /views/templates/admin/input.tpl
- /views/templates/admin/template-form.tpl

Files removed:
-----
- /override_files/controllers/front/ProductController.php

===========================
v 2.7.4 (September 24, 2017)
===========================
- [+] Numeric sliders for features/attributes
- [+] User input fields for numeric sliders
- [+] Radio buttons for filtering options
- [*] Fixed autoindexation on bulk product status update
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/front.css
- /views/css/custom.css
- /views/js/front.js
- /override_files/controllers/admin/AdminProductsController.php
- /views/templates/admin/filter-form.tpl
- /views/templates/hook/amazzingfilter.tpl
- /src/AmazzingFilterProductSearchProvider.php

===========================
v 2.7.3 (August 14, 2017)
===========================
- [*] Compatibility with PS 1.7.2
- [*] Fixed scroll bug in compact view on mobile devices
- [*] Minor fixes and optimizations

Files modified:
-----
- /views/css/front.css
- /views/js/front.js
- /src/AmazzingFilterProductSearchProvider.php

===========================
v 2.7.2 (July 11, 2017)
===========================
- [+] New option: Display prices/images basing on selected attributes
- [*] Removed hidden checkbox options if they are not used on page
- [*] Improved performance on Search results page for PS 1.7
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/js/front.js

===========================
v 2.7.1 (May 29, 2017)
===========================
- [*] Improved checking for stock/existence basing on selected attributes
- [*] Improved overflow-touch-scroll performance in compact view
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/front.css
- /views/js/front.js
- /views/css/back.css
- /views/templates/admin/configure.tpl
- /controllers/front/cron.php

===========================
v 2.7.0 (April 27, 2017)
===========================
- [+] Compact view on mobile devices
- [+] Checkboxes/Selects for price/weight ranges
- [+] Optionally load products on button click
- [+] Optional infinite scroll (instead of standard pagination)
- [*] Show/hide filter blocks by clicking on title
- [*] Show more/less options, if there are too many of them
- [*] Improved range-sliders performance on mobile devices
- [*] Removed asterisk (*) from url params. Primary filter is defined by 1st position
- [*] Fixed compatibility of Autoscrolling to top with Load more button
- [*] Improved criteria selection in admin interface (instant search)
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /readme_en.pdf
- /views/css/back.css
- /views/css/front.css
- /views/css/front-17.css
- /views/js/back.js
- /views/js/front.js
- /views/js/main-page.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/filter-form.tpl
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/dynamic-loading.tpl

Files added:
-----
- /upgrade/install-2.7.0.php
- /views/css/custom.css
- /views/js/jquery.ui.touch-punch.min.js

===========================
v 2.6.1 (March 30, 2017)
===========================
- [*] Improved performance on Search results page
- [*] Improved performance for "checking combinations existence"
- [*] Fixed recognition of initial params for top level category blocks

Files modified:
-----
- /amazzingfilter.php
- /override_files/classes/Product.php

===========================
v 2.6.0 (March 3, 2017)
===========================
- [+] Filter by product condition (new, used, refurbished)
- [+] CRON indexation
- [+] Compatibility with Jolisearch module
- [+] Introduced custom.js/custom.css for easier customization
- [*] Sort category options by position
- [*] Minor fixes, related to sorting products
- [*] Don't interrupt module installation if some overrides were not added
- [*] Admin interface for adding/removing overrides used by module (PS 1.6)
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /controllers/front/myfilters.php
- /views/css/back.css
- /views/js/back.js
- /views/templates/admin/configure.tpl
- /views/templates/admin/filter-form.tpl
- /views/templates/front/my-filters.tpl
- /views/templates/hook/amazzingfilter.tpl
- /readme_en.pdf

Files added:
-----
- /controllers/front/cron.php
- /upgrade/install-2.6.0.php
- /views/js/custom.js

Files moved:
- all php overrides from /override/ to /override_files/. /override/ directory kept

===========================
v 2.5.5 (February 11, 2017)
===========================
- [+] Possibility to add multiple category filter blocks
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/js/front.js
- /views/templates/admin/filter-form.tpl
- /views/templates/hook/amazzingfilter.tpl


===========================
v 2.5.3 (February 2, 2017)
===========================
- [+] Option to include/exclude all products from subcategories, even if they are not associated to current category
- [*] Sort products by position inside checked category, if only one is checked
- [*] PS17: automatically reindex product attributes after bulk generation
- [*] Multistore: Compatibility with shared stock
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/front.css
- /views/js/front.js
- /views/templates/admin/form-group.tpl
- /views/templates/hook/amazzingfilter.tpl

Files added:
-----
- /upgrade/install-2.5.3.php
- /views/js/attribute-indexer.js

===========================
v 2.5.2 (January 14, 2017)
===========================
- [*] PS17: Fix filtering on search results page
- [*] Optimized category tree for filtering templates
- [*] Optimized behavior for price/weight sliders

Files modified:
-----
- /amazzingfilter.php
- /views/css/back.css
- views/js/back.js
- /views/js/front.js
- /views/templates/admin/filter-form.tpl

Files added:
-----
- /views/templates/admin/cat-tree.tpl

===========================
v 2.5.1 (December 18, 2016)
===========================
- [*] Multistore: register/unregister hooks only for current shop context
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/css/icons.css
- /src/AmazzingFilterProductSearchProvider.php

===========================
v 2.5.0 (December 10, 2016)
===========================
- [+] Optionally load icon font. May be useful if theme does not support icon-xx classes
- [*] Compatibility with PS 1.7
- [*] Improved filtering time for prices-drop, new products, bestsellers
- [*] Improved time for sorting by stock, especially for stores with more than 30 000 products
- [*] Fixed load-more button appearance in cases when top pagination is not present in template files
- [*] Automatically deactivate templates for pages without current filter-hook on module installation
- [*] Multistore: register/unregister hooks only for current shop context
- [*] Minor fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/js/front.js
- /controllers/front/myfilters.php
- /views/css/front.css
- /views/css/my-filters.css
- /views/js/front.js
- /views/js/main-page.js
- /views/templates/admin/configure.tpl
- /views/templates/front/my-filters.tpl
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/hook/my-filters-tab.tpl

Files added:
-----
- /src/AmazzingFilterProductSearchProvider.php
- /src/index.php
- /upgrade/install-2.5.0.php
- /views/css/back-17.css
- /views/css/front-17.css
- /views/css/icons.css
- /views/fonts/filterIcons.eot
- /views/fonts/filterIcons.svg
- /views/fonts/filterIcons.ttf
- /views/fonts/filterIcons.woff
- /views/fonts/index.php
- /views/templates/front/content-17.tpl
- /views/templates/front/basic-layout-17.tpl
- /views/templates/hook/dynamic-loading.tpl

===========================
v 2.3.1 (November 5, 2016)
===========================
- [*] Fixed bug with load more button in instant filter on main page
- [*] Fixed bug with indexation when a currency is deleted but still present in database
- [*] Fixed wrong numbers of matches in some complex scenarios when stock is checked for each combination
- [*] Minor code optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/js/main-page.js
- /views/css/front.css

===========================
v 2.3.0 (October 29, 2016)
===========================
- [+] Show/Hide numbers of matches
- [+] Show/Hide/Dim options with zero matches
- [+] Optional foldered layout for subcategories
- [+] Optional autoscroll to top after updating product list dynamically
- [+] Autodetect overriden module files, that require updating
- [*] Improved layout of selected filters block for better buttons response on mobile devices
- [*] Improved instant filter on main page
- [*] Fixed translatable labels for price and weight sliders
- [*] Fixed breadcrumbs path on product page depending on previous visited category PSCSX-8559
- [*] Fixed possible duplicates in dynamic parameter urls
- [*] Consider category group access in customer filters
- [*] Minor fixes for checking combinations existence
- [*] Improved multishop indexation

Files modified:
-----
- /views/templates/hook/amazzingfilter.tpl -------------> important
- /views/css/front.css ---------------------------------> important
- /views/js/front.js -----------------------------------> important
- /views/js/main-page.js -------------------------------> important
- /amazzingfilter.php
- /controllers/front/myfilters.php
- /views/templates/admin/configure.tpl
- /views/templates/admin/filter-form.tpl
- /views/css/back.css
- /views/js/back.js

Files added:
-----
- /views/templates/admin/form-group.tpl
- /override/controllers/front/ProductController.php
- /upgrade/install-2.3.0.php

===========================
v 2.2.0 (July 9, 2016)
===========================
- [*] Fixed filtering on Supplier page
- [*] Fixed indexation bug when BO language is not active in FO
- [*] Fixed stock filtering for products with negative stock
- [*] Re-index products after updating tags in Tag menu
- [*] Misc minor fixes

Files modified:
-----
- /amazzingfilter.php
- /views/js/front.js
- /views/js/main-page.js
- /views/css/back.css

Files added:
-----
- /upgrade/install-2.2.0.php

===========================
v 2.1.2 (May 29, 2016)
===========================
- [+] Highlight filtering button after criteria selection on main page
- [*] Fix #PSCSX-8009, error on saving products when required overrides are out of date
- [*] Included manufacturer_name in filtered results
- [*] Fixed ordering bug on prices drop page
- [*] Fixed initial sorting by date_upd
- [*] Improved auto-indexation on saving products programmatically using $product_obj->save()

Files modified:
-----
- /amazzingfilter.php
- /views/js/main-page.js

===========================
v 2.1.1 (April 30, 2016)
===========================
- [*] Retro-compatibility fix for product indexation
- [*] Minor bug fix for counting stock basing on selected attributes

Files modified:
-----
- /amazzingfilter.php

===========================
v 2.1.0 (April 23, 2016)
===========================
- [+] Configurable out-of-stock behavior: include/exclude/move to the end
- [+] In stock filter
- [+] Easily select hook and change positions on module configuration page
- [*] Updated Admin interface
- [*] Improved indexation (both mass indexation and auto-indexation on product save)
- [*] Exclude inactive categories from filters
- [*] Fixed pagination bug after page refresh
- [*] Optimized count data gathering for large amounts of filters
- [*] Significantly decreased quantity of submitted fields on each ajax request
- [*] Multiple suppliers support
- [*] Retro-compatibility for tags indexation
- [*] Misc code optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/templates/admin/configure.tpl
- /views/templates/admin/filter-form.tpl
- /views/templates/hook/amazzingfilter.tpl
- /views/templates/front/my-filters.tpl
- /views/js/front.js
- /views/js/back.js
- /views/css/back.css
- /controllers/front/myfilters.php
- /override/constollers/admin/AdminProductsController

Files added:
-----
- /documentation_en.pdf
- /views/templates/admin/hook-positions-form.tpl
- /upgrade/install-2.1.0.php

Files removed:
- /readme_en.pdf

===========================
v 2.0.2 (March 09, 2016)
===========================
- [*] Fixed interference with 3rd party carousels of homepage
- [*] Initial uniform styling for filter selects

Files modified:
-----
- /amazzingfilter.php
- /views/templates/hook/amazzingfilter.tpl
- /override/classes/Manufacturer.php
- /override/classes/Product.php
- /override/classes/ProductSale.php
- /override/classes/Search.php
- /override/classes/Supplier.php

===========================
v 2.0.1 (January 30, 2016)
===========================
- [+] Order filter options by numbers in names (e.g. 1mm, 5mm, 10mm)
- [*] Proper ordering on search results
- [*] Misc fixes and optimizations

Files modified:
-----
- /amazzingfilter.php
- /views/templates/admin/filter-form.tpl
- /views/js/front.js

===========================
v 2.0.0 (November 20, 2015)
===========================
- [+] Adjustable customer filters
- [+] Compatibility with search results page
- [+] Compatibility with main page
- [+] Filter by: new products/bestsellers/specials/tags
- [+] Nested categories in filter templates
- [+] Order filter options by name/id/position (if available)
- [+] Dynamic ids/classes for layout. Filter is no longer dependent on hardcoded theme selectors
- [+] Prices are indexed separately for each user group
- [*] Improved filtering algorithm
- [*] Removed "#" from dynamic URLs
- [*] URL params are generated basing on shop URL rewrite settings
- [*] Initial product listing is ready when page loads. No need to run additional ajax request
- [*] Indexed prices are rounded basing on prestashop settings
- [*] Support for product visibility in both/catalog/search/none
- [*] Support for categories group access rights
- [*] Activated the show all products button
- [*] Misc code optimizations
- [*] PSR-2
- [-] Fixed bug of counting matches when count stock is enabled and none of combinations is in stock
- [-] Fixed bug on clicking indexer twice
- [-] Fixed display by position in category
- [-] Fixed out of stock status label
- [-] Removed FF/IE select bug happening when majority of options is hidden

Files modified:
-----
- /amazzingfilter.php
- /views/templates/admin/configure.tpl
- /views/templates/admin/filter-form.tpl
- /views/templates/hook/amazzingfilter.tpl
- /controllers/front/ajax.php
- /override/classes/Manufacturer.php
- /override/classes/Product.php
- /override/classes/ProductSale.php
- /override/classes/Search.php
- /override/classes/Supplier.php
- /views/css/front.css
- /views/css/back.css
- /views/js/front.js
- /views/js/back.js
- /translations/ru.php

Files added:
-----
- /controllers/front/myfilters.php
- /views/templates/front/basic-layout.tpl
- /views/templates/front/my-filters.tpl
- /views/templates/front/no-products.tpl
- /views/templates/front/index.php
- /views/templates/hook/my-filters-tab.tpl
- /views/css/my-filters.css
- /readme_en.pdf
- /views/js/my-filters.js
- /views/js/main-page.js

Files removed:
-----
- /views/templates/hook/result.tpl
- /views/templates/hook/sorting.tpl

===========================
v 1.5.4 (August 5, 2015)
===========================
- [*] Fixed accordion for filterblock on left/right column
- [*] If filter-value is checked and there is no match for it in count_data, it is not hidden.
- [*] Minor code optimizations

===========================
v 1.5.3 (July 27, 2015)
===========================
- [*] Fixed comparator functionality

===========================
v 1.5.2 (July 21, 2015)
===========================
- [+] Count stock for combinations
- [+] Check combinations existence
- [*] Misc code optimizations
