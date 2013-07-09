#!/bin/bash
clear
cat /dev/null > /Applications/MAMP/logs/mysql_sql.log
tail -f /Applications/MAMP/logs/mysql_sql.log
