# Development Environment

## Host Setup

* Install [Docker Desktop](https://www.docker.com/products/docker-desktop)
* Install [Visual Studio Code](https://code.visualstudio.com/) (version >1.35)
* Install the [Remote Develompent](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.vscode-remote-extensionpack) VSC Extension

### Only Windows

In you want to launch `gcloud` commands from the container with the same user of the host: create a symbolic link for the GCloud config folder to match the Unix one (optional)
```
mklink /J gcloud %USERPROFILE%\AppData\Roaming\gcloud
```
If you need to use Google Cloud Repositories you should also patch the `~/.gitconfig` file on host machine (cmd) to use the Unix-style `.gitcookie` path:
```
git config --global http.cookiefile ~/.gitcookies
```


## Configuration

All the environment variables, like DB credentials, are defined in `.devcontainer/docker-compose.yml` file, you can change them before the containers are started.

If you need to import a database on first env creation you can put multiple `.sql/.sql.gz` in the `.devcontainer/dbinit`. More informations on the [MySQL Docker Image Documentation](https://hub.docker.com/_/mysql#initializing-a-fresh-instance).

## Startup

Open the project folder, the one containing the `.devcontainer` folder, with Visual Studio Code and click on the notification button: `Open Folder in Dev Container`. If you missed it you can open the VSC Command Palette `(F1/Shift+Cmd+P)` and search for `Remote-Containers: Reopen Folder in Container`.

After the images are successfully build, the website is available at [https://localhost](https://localhost). The first time the environment is created takes a while to download and build all the necessary components.

## Using Custom Domain

If you nee to use a custom domain or you are in a WP multisite install, you can set up one or more aliases in your `hosts` file: 
```
127.0.0.1 mywebsite.test
127.0.0.1 sub.mywebsite.test
```
The website is now reachable via that domains but you need to replace all the occurrences of `https://localhost` in `.devcontainer/docker-compose.yml` and restart the container. 

*Warning: the `.dev` TLD preloads with HTST enabled by most browsers, you need a valid certificate for all its subdomains. Better to use something else.*

### Multisite

If you are in a multisite you also need to update the `DOMAIN_CURRENT_SITE` in your `wp-config.php` file and the DB via this command:
```
wp search-replace '[your-old-site-domain]' 'mywebsite.test' --all-tables
```
You can use this command in the host machine to get the preformatted list to be appended to your `hosts` file:

```
docker exec [container-name] sh -c "wp site list --fields=url --format=csv | sed -E 's|https?://([^/]*)/(.*)$|127.0.0.1 \1|g' | sed -E s/^url$//"
```

## Additional Info

After the first boot of the dev containers, the MySQL database is initialized and saved in the `.devcontainer/dbdata`. The DB data folder is always mounted from the host and will persist every subsequent container restart. To reset the database you can delete the `dbdata` folder and a fresh DB will be created.

If you need to change any of the configuration parameters or env variables you need to rebuild the containers via the Command Palette: `Remote-Containers: Rebuild Container`.

If your dev team is using Windows and Unix environments, git will mess up file permissions. To fix this behavior you can disable the git file permission analysis via:
```
git config core.filemode false
```
for the current directory, if you want to always disable it by default use the `--global` flag.