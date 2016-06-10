# ---------------------------------------------------------------
# This software is provided under the GNU General Public License
# http://www.gnu.org/licenses/gpl.html
# with Copyright Â© 2009 onwards
#
# Dipl.-Ing. Andreas Hruska
# andreas.hruska@tuwien.ac.at
# 
# Dipl.-Ing. Mag. rer.soc.oec. Katarzyna Potocka
# katarzyna.potocka@tuwien.ac.at
# 
# Vienna University of Technology
# Teaching Support Center
# Gußhausstraße 28/E015
# 1040 Wien
# http://tsc.tuwien.ac.at/
# ---------------------------------------------------------------
# FOR Moodle > 2.7.2
# ---------------------------------------------------------------

README.txt
v.2016-05-15


Gradereport Gradedist
===============

OVERVIEW
================================================================================
    The gradereport gradedist module allows for viewing the distribution of
    student grades for a course regarding the current grade letter boundaries
    as well as changing these to shift the distribution.

REQUIREMENTS
================================================================================
    Originally developed for Moodle 2.7.2

    This module depends on HighCharts for visualizing the grade distribution.
    You can download this module from http://www.highcharts.com/download
    free of charge for non-commercial use.

    Make sure to download the correct package version 4.x and place the core 
    (highcharts.src.js) and the exporting module (exporting.src.js), or if you
    prefer their minified versions (highcharts.js, exporting.js)
    into the folder moodleroot/grade/report/gradedist/js/"

INSTALLATION 
================================================================================
    Every file of the folder/zip goes into
    moodleroot/grade/report/gradedist

    After it you have to run the admin-page of moodle (http://your-moodle-site/admin)
    in your browser. You have to be logged in as admin before.
    The installation process will be displayed on the screen.
    That's all.


USAGE
================================================================================
    1. Within a course, navigate to Administration->Grades->Grade distribution.
    2. The distribution for the current course and grade letter boundaries is shown.
    3. To change the current boundaries, type in values into the new lower boundaries fields.
    4. The chart can be adjusted showing absolute or percent values, the distribution
       for the old and/or the new grade letters.
    5. The current distribution can be downloaded as Excel-Sheet.
    6. The new lower boundaries for the grade letter can be saved via "Change grade letters". 

CHANGELOG
====================================================================================================
        *) 10.06.2016
                #3239: Reflect the group or grouping selection of the chart also in the download
        *) 15.05.2016
                [github][Update] #3267:
                Change recommended download target for highcharts files.
                Adapt code so also their minified versions are accepted.
                Fix exporting bug with non-transparent labels disappearing on image with latest version of highcharts
        *) 15.01.2016
                Fix Bug #2871 Entering "100" invokes an error message and prevents to save the new entries
                Fix Bug #2870 Wrong prefilled new grade boundaries after saving new boundaries
                Hotfix #2637 [github.com] Highcharts JS license:
                    Add text to the *Requirements* section that Highcharts can be
                    downloaded manually for non-commercial use.
        *) 01.12.2015
                Add Feature #2624: Add group and groupings to grade distribution
                chart visualization if groupmode is active
        *) 14.11.2015
                Fix Bug #2771 "Chart displays new and actual values differently
                without having modified new values": make the module compare always with
                "." as decimal point, don't use ",", this might fail for unknown
                reasons.