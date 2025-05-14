CHANGELOG
=========

5.0.0 (2025-05-07)
------------------
* #8284 Moodle 5.0 compatible version

4.5.0 (2024-10-04)
------------------
* #8061 Moodle 4.5 compatible version

4.4.0 (2024-04-22)
------------------
* [FEATURE] #2574 Added PHPUnit tests for lib.php
* #7894 Moodle 4.4 compatible version

4.3.0 (2023-11-14)
------------------
* [FIXED] #7722 Resolved Code Checker Issues
* [FIXED] #7792 Fix duplicates in dropdown menu
* #7730 Moodle 4.3 compatible version

4.2.0 (2023-05-17)
------------------
* [FEATURE] #7574 Improve design of download data functions
* [FEATURE] #7573 Grade columns like in grader report, displaying module type
* #7490 Moodle 4.2 compatible version

4.1.0 (2022-11-17)
------------------
* [FEATURE] #7418 Optimize page structure
* #7144 Moodle 4.1 compatible version

4.0.1 (2022-07-28)
------------------
* [FIXED] #7266 Debug Info if custom user profile fields are used as useridentity

4.0 (2022-03-21)
------------------
* #7050 Moodle 4.0 compatible version

3.11.0 (2021-06-23)
------------------
* #6859 Moodle 3.11 compatible version

3.10.0 (2020-11-19)
------------------
* Moodle 3.10 compatible version

3.9.0 (2020-06-15)
------------------
* Moodle 3.9 compatible version

3.8.0 (2019-11-15)
------------------
* Moodle 3.8 compatible version
* [FIXED] #6394 - Update readme
* [FIXED] #6393 - Fix view permission check
* [FIXED] #6392 - Fix download button getting disabled on form submission

3.7.1 (2019-11-13)
------------------
* [FIXED] #6298 - fix numerical grade letters

3.7.0 (2019-06-04)
------------------
* #5981 Moodle 3.7 compatible version
* [FEATURE] #3888 Replace Highcharts with Chart.js


3.6.2 (2019-02-06)
------------------

* [FIXED] #5908 Using Highcharts 4.x causes weird mixing of percent/absolute values


3.6.1 (2018-12-07)
------------------

* [FIXED] Fixed small bug "unknown variable highcharts"
* [FIXED] Fixed a few settings to get travis-ci to build ok


3.6.0 (2018-11-28)
------------------

* Moodle 3.6 compatible version
* [FIXED] Conditionally load Highcharts src or min version per require-functionality


3.5.0 (2018-05-12)
------------------

* Moodle 3.5 compatible version


3.4.0 (2018-04-24)
------------------

* Moodle 3.4 compatible version
* [FEATURE] #3202 Check use of YUI and analyze rewrite to JQuery/Javascript Modules
* [FEATURE] #5385 Add nullprovider for Privacy API
* [FEATURE] #5114 Remove german lang strings from master (moved to moodledata dir)


3.3.0 (2017-07-27)
------------------

* Moodle 3.3 compatible version
* [FEATURE] #4290 Improve code checker conformity
* [FEATURE] #4295 Add .travis.yml for CI (continuous integration)


3.2.0 (2016-12-05)
------------------

* Moodle 3.2 compatible version
* [FIXED] #3491: Adapt javascript IDs to new Boost theme


3.1.0 (2016-06-10)
------------------

* Moodle 3.1 compatible version
* [FEATURE] #3239: Reflect the group or grouping selection of the chart also in
  the download


3.0.1 (2016-05-15)
------------------

* Moodle 3.0 compatible version
* [CHANGED] #3267: Change recommended download target for highcharts files
  Adapt code so also their minified versions are accepted. Fix exporting bug
  with non-transparent labels disappearing on image with latest version of
  highcharts


2.9.0 (2016-12-01)
------------------

* Moodle 2.9 compatible version
* [FEATURE] #2624: Add group and groupings to grade distribution


2.8.0 (2016-06-25)
------------------

* First release for Moodle 2.8
* [FIXED] #2771 "Chart displays new and actual values differently without
  having modified new values": make the module compare always with "." as
  decimal point, don't use ",", this might fail for unknown reasons
* [CHANGED] #2637 Highcharts JS license: Add text to the *Requirements* section
  that Highcharts can be downloaded manually for non-commercial use
* [FIXED] #2871 Entering "100" invokes an error message and prevents to save
  the new entries
* [FIXED] #2870 Wrong prefilled new grade boundaries after saving new
  boundaries
