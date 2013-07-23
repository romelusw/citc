#!/bin/bash

#  Empties and Tails ("php_error") log file   #
#                                             #
#  @author Woody Romelus                      #
#                                             #
clear
cat /dev/null > /Applications/MAMP/logs/php_error.log
#cat /dev/null > /Applications/MAMP/logs/romelus_debug.log
rm -rf /Applications/MAMP/tmp/php/*
tail -f /Applications/MAMP/logs/php_error.log
