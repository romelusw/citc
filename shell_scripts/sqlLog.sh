#!/bin/bash

#  Empties and Tails ("mysql") log file       #
#                                             #
#  @author Woody Romelus                      #
#                                             #
clear
cat /dev/null > /Applications/MAMP/logs/mysql_sql.log
tail -f /Applications/MAMP/logs/mysql_sql.log
