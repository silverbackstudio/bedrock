#! /bin/bash
# SilverbackStudio AppEngine Project Setup 
read -p "Project ID: " GCLOUD_PROJECT_ID
read -p "Project name: " GCLOUD_PROJECT_NAME

# Create GCloud Project
gcloud projects create $GCLOUD_PROJECT_ID --name=$GCLOUD_PROJECT_NAME

# Set the project in the flags file 
echo "      --project: ${GCLOUD_PROJECT_ID}" >> flags.yml

# Enable AppEngine
gcloud services enable appengine.googleapis.com --project=$GCLOUD_PROJECT_ID

# Link to billing account
gcloud alpha billing projects link $GCLOUD_PROJECT_ID --billing-account=003779-F1328B-4143F9

# Set up git repo
gcloud source repos create website --flags-file=flags.yml
WEBSITE_REPO_URL=$(gcloud source repos list --flags-file=flags.yml --format="value(url)" --filter="name~website")
git remote add gcloud $WEBSITE_REPO_URL
git remote set-url --push origin no-pushing
git push --set-upstream gcloud

# Set Project Permissions
IAM_ETAG=$(gcloud projects get-iam-policy ${GCLOUD_PROJECT_ID} --format="value(etag)")
echo $IAM_ETAG >> iam.yml
gcloud set-iam-policy $GCLOUD_PROJECT_ID iam.yml

# Verify domain
# gcloud domains verify example.com