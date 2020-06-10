#! /bin/bash
# SilverbackStudio AppEngine Project Setup 
read -e -p "Project ID: " -i $CLOUDSDK_CORE_PROJECT CLOUDSDK_CORE_PROJECT
read -p "Domain: " WEBSITE_DOMAIN
read -p "Billing Account (leave empty to skip): " BILLING_ACCOUNT
read -e -p "Cloud Region: " -i "europe-west3" GOOGLE_CLOUD_REGION
read -e -p "Repo Name: " -i "wp-website" GOOGLE_CLOUD_REPO_NAME
read -e -p "Cloud SQL instance name: " -i "wp-website" GOOGLE_CLOUD_SQL_NAME
read -e -p "Database Instance Tier: " -i "db-f1-micro" GOOGLE_CLOUD_SQL_TIER
read -e -p "Database Name: " -i "wordpress" GOOGLE_CLOUD_SQL_DB_NAME
read -e -p "Database User: " -i "wordpress_user" GOOGLE_CLOUD_SQL_DB_USER

echo "Creating project in region:" $GOOGLE_CLOUD_REGION

# Enable AppEngine
gcloud services enable appengine.googleapis.com sourcerepo.googleapis.com

# Link to billing account if specified
[ ! -z "$BILLING_ACCOUNT" ] && gcloud beta billing projects link $CLOUDSDK_CORE_PROJECT --billing-account=$BILLING_ACCOUNT

# Set up git repo
gcloud source repos create $GOOGLE_CLOUD_REPO_NAME
WEBSITE_REPO_URL=$(gcloud source repos list --format="value(url)" --filter="name~wp-website")
git remote add gcloud $WEBSITE_REPO_URL
git remote set-url --push origin no-pushing
git push --set-upstream gcloud

DB_ROOT_PASSWORD=$(openssl rand -base64 24)
DB_USER_PASSWORD=$(openssl rand -base64 24)
DB_PREFIX=$(openssl rand -base64 2)
DB_INSTANCE_NAME=$CLOUDSDK_CORE_PROJECT:$GOOGLE_CLOUD_REGION:$GOOGLE_CLOUD_SQL_NAME

# Create a basic SQL instance
gcloud sql instances create $GOOGLE_CLOUD_SQL_NAME --root-password=$DB_ROOT_PASSWORD --region=$GOOGLE_CLOUD_REGION --tier=$GOOGLE_CLOUD_SQL_TIER --database-version='MYSQL_5_7' --backup 

echo "Started SQL instance $DB_INSTANCE_NAME with root password: " $DB_ROOT_PASSWORD

wget https://dl.google.com/cloudsql/cloud_sql_proxy.linux.amd64 -O cloud_sql_proxy
chmod +x ./cloud_sql_proxy
./cloud_sql_proxy -dir=/cloudsql -instances=$DB_INSTANCE_NAME=tcp:3307 & SQL_PID=$!
sleep 5
mysql -u root --password=$DB_ROOT_PASSWORD -h 127.0.0.1 --port 3307 -e "\
    CREATE DATABASE $GOOGLE_CLOUD_SQL_DB_NAME; \
    GRANT ALL ON $GOOGLE_CLOUD_SQL_DB_NAME.* TO '$GOOGLE_CLOUD_SQL_DB_USER'@'%' IDENTIFIED BY '$DB_USER_PASSWORD';" \
    && echo "Created SQL database with user password: " $DB_USER_PASSWORD
kill $SQL_PID

rm cloud_sql_proxy

# Replace vars in env
sed -i -e "s/DB_NAME: ''/DB_NAME: '$GOOGLE_CLOUD_SQL_DB_NAME'/g" env_variables.yaml
sed -i -e "s/DB_USER: ''/DB_USER: '$GOOGLE_CLOUD_SQL_DB_USER'/g" env_variables.yaml
sed -i -e "s/DB_PASSWORD: ''/DB_PASSWORD: '$DB_USER_PASSWORD'/g" env_variables.yaml
sed -i -e "s/DB_HOST: '.*'/DB_HOST: ':\/cloudsql\/$DB_INSTANCE_NAME'/g" env_variables.yaml
sed -i -e "s/DB_PREFIX: '.*'/DB_PREFIX: 'wp${DB_PREFIX}_'/g" env_variables.yaml

sed -i -e "s/WP_HOME: ''/WP_HOME: 'https:\/\/$WEBSITE_DOMAIN'/g" env_variables.yaml
sed -i -e "s/WP_SITEURL: ''/WP_SITEURL: 'https:\/\/$WEBSITE_DOMAIN\/wp'/g" env_variables.yaml

# # Create AppEngine App
gcloud app create --region=$GOOGLE_CLOUD_REGION

# # Verify domain
gcloud domains verify $WEBSITE_DOMAIN

