Grade Distribution
==================

This file is part of the gradereport_gradedist plugin for Moodle - <http://moodle.org/>

*Author:*    Andreas Krieger, Günther Bernsteiner

*Copyright:* 2014 [Academic Moodle Cooperation](http://www.academic-moodle-cooperation.org)

*License:*   [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

The Grade Distribution report visualizes the grades of students in a course. Changes to letter
grades (i.e. the boundary of a grade) can be analysed visually, saved and therefore overwritten if
necessary.


Example
-------

Usually letter grades of a course should be communicated to the students at the beginning of a
term. Occasionally it occurs that changes to letter grades are necessary. The grade distribution
visualizes the grades of students. Changes to letter grades (i.e. the boundary of a grade) can be
analysed visually, saved and therefore overwritten if necessary. By means of an alternating graphic
chart changes can be seen immediately.


Requirements
------------

The plugin is available for Moodle 2.7+. This version is for Moodle 3.3.

The module requires the external [Highcharts JavaScript
library](<http://www.highcharts.com/products/highcharts>), available free of charge for
non-commercial use.


Installation
------------

* Copy the module code directly to the *moodleroot/grade/report/gradedist* directory.

* Download version 4.x of the Highcharts library from <http://www.highcharts.com/download>. Copy
  both, the core module *highcharts.src.js* from the *js* subdirectory and the *exporting.src.js*
  module from the *js/modules* subdirectory into the directory *grade/report/gradedist/js*.

* Log into Moodle as administrator.

* Open the administration area (*http://your-moodle-site/admin*) to start the installation
  automatically.


Admin Settings
--------------

An administrator can adjust the default settings instance-wide for the grade distribution in the
general settings page. The type of grade display can be specified and one can choose from the
following:

* Letter
* Letter (percentage)
* Letter (real)
* Percentage
* Percentage (letter)
* Percentage (real)
* Real
* Real (letter)
* Real (percentage)


Documentation
-------------

You can find a cheat sheet for the plugin on the [AMC
website](http://www.academic-moodle-cooperation.org/en/modules/grade-distribution/) and a video
tutorial in german only in the [AMC YouTube
Channel](https://www.youtube.com/c/AMCAcademicMoodleCooperation).


Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/academic-moodle-cooperation/moodle-gradereport_gradedist/issues).
Please provide a detailed bug description, including the plugin and Moodle version and, if
applicable, a screenshot.

You may also file a request for enhancement on GitHub. If we consider the request generally useful
and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the
resources to provide detailed support.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
