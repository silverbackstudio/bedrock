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

## Configuration

All the environment variables, like DB credentials, are defined in `.devcontainer/docker-compose.yml` file, you can change them before the containers are started.

If you need to import a database on first env creation you can put multiple `.sql/.sql.gz` in the `.devcontainer/dbinit`. More informations on the [MySQL Docker Image Documentation](https://hub.docker.com/_/mysql#initializing-a-fresh-instance).

## Startup

Open the project folder, the one containing the `.devcontainer` folder, with Visual Studio Code and click on the notification button: `Open Folder in Dev Container`. If you missed it you can open the VSC Command Palette `(F1/Shift+Cmd+P)` and search for `Remote-Containers: Reopen Folder in Container`.

After the images are successfully build, the website is available at [http://localhost:8080](http://localhost:8080). The first time the environment is created takes a while to download and build all the necessary components.

## Additional Info

After the first boot of the dev containers, the MySQL database is initialized and saved in the `.devcontainer/dbdata`. The DB data folder is always mounted from the host and will persist every subsequent container restart. To reset the database you can delete the `dbdata` folder and a fresh DB will be created.

If you need to change any of the configuration parameters or env variables you need to rebuild the containers via the Command Palette: `Remote-Containers: Rebuild Container`.