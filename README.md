# NodeYaml

Drupal 8 allow you export and import the configuration as text files(yaml
format), but not content.

This module allow you export and import the content of your nodes as yaml files,
so you can put it under version control like your configuration, and since yaml
is a friendy format, you can edit it too.

## Use:

drush export-nodes
Will export all nodes.

drush import-nodes
Will import all nodes.
