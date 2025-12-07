#!/bin/bash

sudo find storage -not -path "**/.gitignore" -exec chmod 777 {} \;
sudo find storage -not -path "**/.gitignore" -exec chown www-data:www-data {} \;
sudo adduser "$USER" www-data
