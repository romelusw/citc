#!/usr/bin/env python3

import http.client, os.path, urllib.parse

'''
Build Script that automates the necessary actions before committing the code.
'''

'''
Compresses javascript files Using the Google Closure Compiler API
'''
def retrieveCompressJavaScript(filePath):
    if os.path.isfile(filePath):

        params = urllib.parse.urlencode([
            ('js_code', open(filePath, "r").read()),
            ('compilation_level', 'SIMPLE_OPTIMIZATIONS'),
            ('output_format', 'text'),
            ('output_info', 'compiled_code'),
            ])

        headers = { "Content-type": "application/x-www-form-urlencoded" }
        conn1 = http.client.HTTPConnection("closure-compiler.appspot.com")
        conn1.request("POST", "/compile", params, headers)
        resp = conn1.getresponse()
        data = resp.read()
        conn1.close()
        return data

'''
Updates the contents of a file with the specified new content
'''
def overwriteFile(filePath, text):
    if os.path.isfile(filePath):
        f = open(filePath, "w")
        f.write(text.decode("utf-8"))
        f.close()

'
================================ TASKS ===============================
'
# Compress JavaScript files
overwriteFile("/Applications/MAMP/htdocs/citc/javascript/min/functions-min.js", retrieveCompressJavaScript("/Applications/MAMP/htdocs/citc/javascript/functions.js"))
