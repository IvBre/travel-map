# Travel map

This application is used for displaying a world map with user travels.
User can log in using OAuth and import its events from Google Calendar 
 (more to come in the future). Imported events will be displayed on the world map as pins.

## Installation
1. Clone this repo to your machine
2. Run `composer install` in the root folder
3. Copy `app/config/parameters.json.dist` to `app/config/parameters.json` 
 and update the parameters.

### Docker
Docker is available for this application for you to use. Run these commands to start
 the docker machine and containers:
 
```
$ docker-machine start
$ eval $(docker-machine env)
$ docker-compose up -d
```

Please not that this will start the `default` docker machine.

Edit your `/etc/hosts` file and add entry:

```
192.168.99.100 localhost
```

Please note that this IP address is for the default machine, 
 and if it does not work for you type in:
 
```
$ docker-machine ip
```

to find the correct IP of your current active machine.

Run [your local application](http://localhost) in the browser.
