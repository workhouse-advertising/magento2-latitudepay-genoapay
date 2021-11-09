#!/usr/bin/env bash
set -e
REPO_URL=$(git config --get remote.origin.url)

RE="^(https|git)(:\/\/|@)([^\/:]+)[\/:]([^\/:]+)\/(.+).git$"

if [[ $REPO_URL =~ $RE ]]; then
    GITHUB_USER=${BASH_REMATCH[4]}
    GITHUB_REPO=${BASH_REMATCH[5]}
fi
EXISTING_TAGS=(`echo $(git tag -l)`);
COMPOSER_TAG=$(grep -o '^ *"version": *"[0-9\.]*"' composer.json|awk '{print $2}'|sed -e 's/"\(.*\)"/\1/g')
for constant in $EXISTING_TAGS
do
  if [ "$constant" = "$COMPOSER_TAG" ]; then
    echo "The tag exists already: ${COMPOSER_TAG}";
    exit;
  fi
done
echo "Create new tag: ${COMPOSER_TAG}"
CHANGELOG_JSON="Create new tag: ${COMPOSER_TAG}"
API_JSON=$(printf '{"tag_name": "%s","target_commitish": "master","name": "%s","body": "%s","draft": false,"prerelease": false}' $COMPOSER_TAG $COMPOSER_TAG "$CHANGELOG_JSON")

curl -H "Authorization: token ${GITHUB_TOKEN}" --data "$API_JSON" https://api.github.com/repos/${GITHUB_USER}/${GITHUB_REPO}/releases

echo "*** FIN ***"