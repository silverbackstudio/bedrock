# Deploy Instructions

## 1. Create Google Cloud Project

### 1.1 Create a new GCP Project (if not already present)
```bash
gcloud projects create [project-name] --name="[Project Nice Name]" --set-as-default

# Otherwise set an existing project as default
gcloud config set project [project-name]
```

### 1.2 Inizialize App Engine in the preferred region.
WARNING: the region **can't be changed** after. You can use [GCPing](http://www.gcping.com/) to find the one with the lowest latency from your country.

```bash
gcloud app create --region=[europe-west3]
```

## 2. Create Database

### 2.1 Create a new database instance (if not already present)

This will create a new DB instance named `wp-website` with a `root` user password set to [password] (replace it with a 32-char random password). 

*IMPORTANT: Choose the **same region of the GAE app** or the inter-region network latency will impact you site performance massively.*

```bash
gcloud sql instances create [wp-website] --root-password=[password] --region=[europe-west3] --tier=[db-f1-micro]
```

It might take up to 10 mins to create the instance.

### 2.2 Create database and user

Connect to the instance

```
gcloud sql connect [wp-website] --user=root
```
You will be asked for the `root` user password set up in the step 2.1).


Create the database and an user that can only R/W to this DB.
In this case we will create a database named `wordpress` and a user `wordpress_user` with the password `[mysql-user-password]` (replace it with a 32-char random password).

```sql
CREATE DATABASE wordpress; GRANT ALL ON wordpress.* TO 'wordpress_user'@'%' IDENTIFIED BY '[mysql-user-password]';
```

and `exit`.

## 3. Setup the GAE environment

Open the `/env_variables.yaml` file and configure:

### 3.1 Connect to Database

Insert the instance, DB and user credentials set up in the steps 2.1) and 2.2) in the corresponding vars:

```yaml
DB_NAME: 'wordpress'
DB_USER: 'wordpress_user'
DB_PASSWORD: '[mysql-user-password]'
DB_HOST:  ':/cloudsql/project-name:europe-west3:wp-website' # You can get the DB connection string via the command `gcloud sql instances describe [wp-website] --format="value(connectionName.scope())"`
```

### 3.2 Change the DB tables prefix

Replace the default `wp_` prefix with something custom, to prevent SQL injection attacks. Usually adding 2-3 random chars before the `_` it's enough.

```yaml
DB_PREFIX: 'wpxx_'
```

### 3.3 Set the website URL

For this bedrock installation the `siteurl` always has a `/wp` suffix.

```yaml
WP_HOME: 'https://[project-name].appspot.com'
WP_SITEURL: 'https://[project-name].appspot.com/wp'
```

### 3.4 Generate the SALT keys for this installation

1. Visit [https://roots.io/salts.html]()
2. Copy the `Env format` block: the var names must be uppercase.
3. Replace the empty vars with this whole block
4. Replace each var's `='` assign with the `: '` assign

```yaml
AUTH_KEY: '[...]'
SECURE_AUTH_KEY: '[...]'
LOGGED_IN_KEY: '[...]'
# ...
```

Replace other WP vars accordingly..

## 4. Deploy the app to App Engine

Test the app in the local environment before deploying to production.

### 4.1 Start the deployment of the app and the cron jobs:

```bash
gcloud beta app deploy app.yaml cron.yaml --no-cache
```

_The beta's `--no-cache` flag is actually REQUIRED due to [this](https://stackoverflow.com/questions/58343319/deploy-gae-app-with-some-composer-packages-outside-vendor-folder/62921302#62921302) bug in GAE composer installation procedure_

Wait for the app to be successfully deployed. 

### 4.2 Install and configure Wordpress

1. Visit the app URL setup above and wordpress should redirect you to the installation page. Follow the wizard and don't forgot the admin password.
2. Activate all the needed plugins
3. Activate the theme and initialize it by visiting the Customizer page (if required).
4. Connect Jetpack (if required)
5. Add users/admins

### 4.3 Set up uploads via Google Cloud Storage

1. Check that you have enabled the `Google cloud Storage` plugin
2. Log in the Wordpress Admin and from the Main menu select ` Settings > Media `
3. In the `Bucket name for media upload` field insert your GCS bucket url. If you don't need to use a custom bucket, GAE creates a default bucket named `gs://[project-name].appspot.com`

Allow anonymous users to read uploads from the default bucket

```
gsutil defacl ch -u AllUsers:R gs://[project-name].appspot.com
```

## 5. Import and setup your contents

Now it's time to set-up your theme and import o write your contents. 

## 6. Set you custom domain

Replace the `*.appspot.com` URL with a custom domain.

### 6.1 Change the `env_variables.yaml` file WP urls:

```yaml
WP_HOME: 'https://example.com'
WP_SITEURL: 'https://example.com/wp'
```

### 6.2 Deploy the change to GAE

```bash
gcloud app deploy app.yaml
```

### 6.3 Set up the custom domain in the GAE app 

1. Visit the [App Engine Custom Domain](https://console.cloud.google.com/appengine/settings/domains) page
2. Click on [`Add Custom Domain`] button
3. Follow the GAE domain configuration wizard
4. Insert/replace the provided records in the domain DNS zone
5. Wait 5-10 mins for the SSL Certificates 

### 6.4 Search and replace for old URLs
