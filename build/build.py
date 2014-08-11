#!/usr/bin/env python3

import http.client, os.path, urllib.parse

'''
Build Script that automates the necessary actions before committing the code.
'''

'''
Compresses javascript files Using the Google Closure Compiler API
'''
def compressJavaScript(filePath):
    if os.path.isfile(filePath):
        params = [
            ('js_code', open(filePath, "r").read()),
            ('compilation_level', 'SIMPLE_OPTIMIZATIONS'),
            ('output_format', 'text'),
            ('output_info', 'compiled_code'),
        ]
        sendPOST("closure-compiler.appspot.com", "/compile", params)

'''
Compresses css files using CSS minifier service
'''
def compressCSS(filePath):
    if os.path.isfile(filePath):
        params = [
            ('input', open(filePath, "r").read())
        ]
        return sendPOST("cssminifier.com", "/raw", params)

'''
Sends a POST requests, returning the reponse from the server
'''
def sendPOST(domain, path, params):
    headers = { "Content-type": "application/x-www-form-urlencoded" }
    params = urllib.parse.urlencode(params)
    conn1 = http.client.HTTPConnection(domain)
    conn1.request("POST", path, params, headers)
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

'''
================================ TASKS ===============================
'''
# Compress CSS files
print(compressCSS("/Applications/MAMP/htdocs/citc/css/styles.css"))
# Compress JavaScript files
#overwriteFile("/Applications/MAMP/htdocs/citc/javascript/min/functions-min.js", compressJavaScript("/Applications/MAMP/htdocs/citc/javascript/functions.js"))
