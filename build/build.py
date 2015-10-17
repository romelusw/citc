#!/usr/bin/env python3
import http.client, os.path, urllib.parse, time

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
        return sendPOST("closure-compiler.appspot.com", "/compile", params)

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
    else:
        raise IOError("%s does not exist" % filePath)

if __name__ == "__main__":
    start = time.time()
    # Compress CSS files
    overwriteFile("/Applications/MAMP/htdocs/citc/develop/css/min/styles-min.css", compressCSS("/Applications/MAMP/htdocs/citc/develop/css/styles.css"))
    print("Compressing CSS files %.2f seconds" % (time.time() - start))

    start = time.time()
    # Compress JavaScript files
    overwriteFile("/Applications/MAMP/htdocs/citc/develop/javascript/min/functions-min.js", compressJavaScript("/Applications/MAMP/htdocs/citc/develop/javascript/functions.js"))
    print("Compressing Javascript files %.2f seconds" % (time.time() - start))
    print("... Complete ...")
