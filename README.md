
<p align="center">
<img src="logo.png" height="300">
<h1 align="center">Browser-Mirror PHP API</h1>
</p>

This is a basic, **unsecured** webservice API written in PHP for [Browser-Mirror](https://github.com/Pamblam/browser-mirror).

## Set up

Firstly, you need to make sure PHP can run sudo without a prompt. One method (not the best method) is to...

 1. Create a PHP script: `<?php exec('whoami', $out); echo $out[0];`. Run it in the same environment your API will run. (From the web server). This will give you the user PHP runs under.
 2. Run `sudo visudo` from the command line to edit the sudoers file. Add this line (changing `myuser` to the user your PHP script gave): `myuser ALL=(ALL) NOPASSWD: ALL`. Hit `esc` and type `:x` to save and exit. 

Now you need to provide the proper paths to both Node and Browser mirror.

 1. Run `which node` and `which bm` from the command line to get the full paths to node and browser-mirror, respectively.
 2. In `api.php` provide those paths to `BrowserMirror::setBMPath()` and `BrowserMirror::setNodePath()`, respectively.

Finally: Secure it. That is beyond the scope of this project. The `api.php` file is very straight forward. Add your own auth code there.

## Endpoints

 - `api.php?action=get_status`: Get the status of the BM Server. (Running or not.)
 - `api.php?action=get_logs`: Get an array of containing the dates and messages of each record logged by the server.
 - `api.php?action=start_server&port=1337`: Start the server. The `port` parameter is optional and defaults to 1337.
 - `api.php?action=stop_server`: Stop the server.