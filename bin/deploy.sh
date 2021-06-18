#!/bin/bash

INVALID_PARAMETERS="\033[1;31mError:\033[0m Please make sure you've indicated correct parameters."

# Path to plugin directory on the remote server
PLUGIN_DIR="/path/on/remote/server/to/wp-content/plugins/${CIRCLE_PROJECT_REPONAME}"

# Set Remote server SSH credentials (If this is a public Repo, you will want to set these as CircleCI environment variables)
SSH_CREDS="user@host"

# Add the server IP to the known_hosts file
# Required for CircleCI to allow SSH connections to remote server
# example: ssh-keyscan 123.456.789.123 >> ~/.ssh/known_hosts
ssh-keyscan REMOTE.SERVER.IP >> ~/.ssh/known_hosts

cd ${HOME}/project/build/${CIRCLE_PROJECT_REPONAME}

if [ $# -eq 0 ]
	then
		echo -e ${INVALID_PARAMETERS}
elif [ $1 == "--dry-run" ]
	then
		echo "Running dry-run deployment."
		rsync --stats --dry-run --delete --progress -avz -e "ssh -p22" ./ ${SSH_CREDS}:${PLUGIN_DIR}
elif [ $1 == "live" ]
	then
		echo "Running actual deploy"
		rsync --stats --delete -avz -e "ssh -p22" ./ ${SSH_CREDS}:${PLUGIN_DIR}
else
	echo -e ${INVALID_PARAMETERS};
fi