# linux-file-sweaper
For lack of a better project title, linux-file-sweaper is a command line tool for linux users to keep thier home directory cleaner. Origionally it was intended to be ran on the Downloads folder to make sure things got to their aproprate folders.

# How to install?
Currently this is in the beginning stages, and requires some manual work to get installed.

Dependents: php5-cli
Download the tarball and extract it.
Append "#!/usr/bin/php" to the top of the main.php file prior to the line with "<?php".
Then change the file to be executible (sudo chmod +x [file]).

from the terminal you should be able to use the script... hopefully.

# Commands
-V [setting]            VERBOSE: a boolean flag denoting if echo should be used to output what the script is doing.
-C [config location]    CONFIG: A string of the location of your config.json file.
-D [Directory]          DIRECTORY: A string of the directory in the homefolder you wish to scan and potentally sweap.
