#!/bin/bash

set -e

## CONFIG
VERSION=0.0.1
DOCKER_HUB_ACCOUNT=zotornit
DOCKER_HUB_IMAGE=t3ter-upload


docker build --file ./Dockerfile.upload -t $DOCKER_HUB_ACCOUNT/$DOCKER_HUB_IMAGE:$VERSION .
docker image tag $DOCKER_HUB_ACCOUNT/$DOCKER_HUB_IMAGE:$VERSION $DOCKER_HUB_ACCOUNT/$DOCKER_HUB_IMAGE:latest

# push latest and $VERSION
docker image push $DOCKER_HUB_ACCOUNT/$DOCKER_HUB_IMAGE




