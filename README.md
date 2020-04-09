# TER-Upload - Upload TYPO3 extensions with ease

## What does it do

Easy way to automate the release of TYPO3 extensions to the TER using webhooks

### UPLOAD

upload.php script which allows the upload of a TYPO3 extension using the commandline.

```bash
# Upload a TYPO3 extension like this
php upload.php extension_dir=/some/path upload_comment="This is my comment" user="userxy" password=mysecretpassword123

# or with a comment file
php upload.php extension_dir=/some/path upload_comment_file="/path/to/file" user="userxy" password=mysecretpassword123
```

It is better use the docker image to fire this command. 
    By default the docker image expects the extension directory be mounted to `/extension`
```bash
docker run -it --rm \
  -v "/path/to/extension":/extension \
  -e TYPO3_USER='userxy' \
  -e TYPO3_PASSWORD='mysecretpassword123' \
  zotornit/t3ter-upload upload upload_comment="This is my comment"
```

**Available ENV vars / CL arguments:**

* `TYPO3_USER` / `user`: typo3.org username
* `TYPO3_PASSWORD` / `password`: typo3.org password
* `TYPO3_EXTENSION_DIR` / `extension_dir`: Path to extension directory
* `TYPO3_UPLOAD_COMMENT` / `upload_comment`: Last upload comment
* `TYPO3_UPLOAD_COMMENT_FILE` / `upload_comment` : Last upload comment from file. Takes precedence over `TYPO3_UPLOAD_COMMENT` / `upload_comment`

### WEBHOOK

Simple webhook implementation to consume a webhook and create a small json file which 
    can be parsed by the **autoupload** script.

Only a push event, which pushes a `tag` is taken into account. Other push events are ignored.
The `tag` will be the TER extension version and must match the `$EM_CONF['tx_extkey']['version]` 
    of your TYPO3 extension. TER does not allow uploading the same version twice (no replacing!).

Git points every `tag` to a commit and the commit's `description` will be used as `Last upload comment` 
    for the TYPO3 extensions website: https://extensions.typo3.org/extension/. 
    _This behaviour might change in the future. Depends on how things work out in real cases._
    
**Your workflow as a developer**

```bash
# 1. Do changes to your great extension and commit
# 2. Change $EM_CONF['tx_extkey']['version] to new version. Let's say: 3.1.1
# 3. Commit the version change and write a nice description as upload comment

git add ext_emconf.php

# EITHER use the default editor:
# Note: 1st row the commit message, 2nd row should be empty, 3rd row starts the commit's description/comment and may be multiline.
git commit

# OR write everything as single command:
# Depends on your shell, but in most cases this should work. End multiline input with `'`
git commit -m 'Set version to 3.1.1
> 
> This is the upload comment for this release.
> And this is another line.'

# 4. Then push the commit (a push event webhook will be triggered, but ignored by the script)
git push

# 5. Add the tag
git tag -a 3.1.1

# 6. Push the tag (a push event webhook will be triggered, this time the script consumes it)
git push origin 3.1.1
```

_Currently only Github is supported. However adding your own custom webhook is easy to implement. 
Look at the sources._

**Setup**

1. Place the webhook folder contents to your website root folder and adjust the `config.php` to your needs.
2. Create a webhook in the settings section of your GitHub project. Keep defaults, add your `secret` and `url`.

The script will now create files containing json data every time a valid webhook request has been sent. 
    The created files can be parsed by the **autoupload** script. 

The script silently logs errors and always answers with a 200 OK.

### DOCKER IMAGES

Putting **upload** and **webhook** together using docker images:

There are two images `zotornit/t3ter-webhook` and `zotornit/t3ter-upload` you can use.

**zotornit/t3ter-webhook**

Apache2 server with webhook API.
    There is no built in `SSL/TLS support`. 
    If you need SSL/TLS consider using a proxy or adjust the docker image to your needs. 
    However the webhook secret is never send in plaintext with the request, 
    the secret is only used to hash the payload for further verification on the client side.

```bash
docker run -it --rm \
  --name t3ter_webhook \
  -p 80:80 \
  -v "/var/webhook/data":/var/webhook/data \
  -e WH_GITHUB_SECRET='YOUR_GITHUB_SECRET' \
  zotornit/t3ter-webhook
```

Add the Webhook to your project.

**zotornit/t3ter-upload - autoupload**

You already know this image from the **UPLOAD** section above. 
    However, it comes with another tool. The `autoupload` script is able to parse the webhook json files.
    When a valid file is found, the `upload` script is fired and the extension gets uploaded.

```bash
docker run -it --rm \
  -v "/var/webhook/data":/var/webhook/data \
  -e TYPO3_USER='userxy' \
  -e TYPO3_PASSWORD='mysecretpassword123' \
  zotornit/t3ter-upload autoupload
```

It is not necessary to provide `TYPO3_EXTENSION_DIR` / `extension_dir` since the script clones your git repository and handles things internally.

I recommend creating two shell scripts for `zotornit/t3ter-webhook` and `zotornit/t3ter-upload`. 
    The latter one should run maybe once per hour as cronjob.
    
**Happy coding!**
