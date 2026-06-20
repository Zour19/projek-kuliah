#!/usr/bin/env bash
set -euo pipefail

# publish.sh
# Usage: ./publish.sh [repo-url] [git-email]
# Example: ./publish.sh git@github.com:Zour19/myrepo.git zour@example.com

GITNAME="Zour19"
REPO_URL=${1:-""}
GIT_EMAIL=${2:-""}

if [ -z "$GIT_EMAIL" ]; then
  read -r -p "Enter git author email to use for commits: " GIT_EMAIL
fi

if [ -z "$REPO_URL" ]; then
  read -r -p "Enter repo remote URL (ssh or https), e.g. git@github.com:Zour19/myrepo.git: " REPO_URL
fi

echo "Using git name: $GITNAME"
echo "Using git email: $GIT_EMAIL"
echo "Using repo: $REPO_URL"

if [ ! -d .git ]; then
  git init
fi

git config user.name "$GITNAME"
git config user.email "$GIT_EMAIL"

git add .
if git commit -m "Initial commit — by $GITNAME" 2>/dev/null; then
  echo "Committed changes"
else
  echo "No changes to commit"
fi

git branch -M main 2>/dev/null || true
git remote remove origin 2>/dev/null || true
git remote add origin "$REPO_URL"

echo "Pushing to remote..."
git push -u origin main

echo "Done. Repository pushed as $GITNAME <${GIT_EMAIL}>"
