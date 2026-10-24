# HESTIA WEBSERVER

The aim here is to use a website as a main command and commnication hub for the Hestia robot.

The site won't run on the robot itself but on a local server. For the time being, this is going to be my laptop, and I'll access the site via `localhost`. But I can foresee this being moved to a local network server.

Currently, the only app is an HTML/JavaScript front end using WebSockets to communicate with a comms hub on Hestia (see [this repo](https://github.com/mspeculatrix/Hestia_Pico_WS_Server)).

The server runs as a Docker container, based on the PHP-Apache image.

As well as front-end apps, it will probably provide an API - either via PHP or possibly a Node.js server. Only time will tell.

## SUPPORT THIS PROJECT

If you find this project in any way interesting or useful, you could consider [buying me a coffee](https://ko-fi.com/zolachrome).
