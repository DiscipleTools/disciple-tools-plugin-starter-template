#!/bin/bash

plugin_name="My Awesome Plugin"
plugin_class="My_Awesome_Plugin"
plugin_function="my_awesome_plugin"
plugin_path="my-awesome-plugin"
plugin_post_type="awesome_plugin"

echo -e "\nReplacing string 'Plugin Starter Template'... for '$plugin_name'."
grep -rl --exclude-dir=*.git "Plugin Starter Template" | grep -Ev customize.sh | LANG=C xargs sed -i '' "s/Plugin Starter Template/${plugin_name}/g"

echo -e "Replacing string 'Disciple_Tools_Plugin_Starter_Template'... for '$plugin_class'."
grep -rl --exclude-dir=*.git "Disciple_Tools_Plugin_Starter_Template" | grep -Ev customize.sh | LANG=C xargs sed -i '' "s/Disciple_Tools_Plugin_Starter_Template/${plugin_class}/g"


echo -e "Replacing string 'disciple_tools_plugin_starter_template'... for '$plugin_function'."
grep -rl --exclude-dir=*.git "disciple_tools_plugin_starter_template" | grep -Ev customize.sh | LANG=C xargs sed -i '' "s/disciple_tools_plugin_starter_template/${plugin_function}/g"

echo -e "Replacing string 'disciple-tools-plugin-starter-template'... for '$plugin_path'."
grep -rl --exclude-dir=*.git "disciple-tools-plugin-starter-template" | grep -Ev customize.sh | LANG=C xargs sed -i '' "s/disciple-tools-plugin-starter-template/${plugin_path}/g"

echo -e "Replacing string 'starter_post_type'... for '$plugin_post_type'."
grep -rl --exclude-dir=*.git "starter_post_type" | grep -Ev customize.sh | LANG=C xargs sed -i '' "s/starter_post_type/${plugin_post_type}/g"

echo -e "\nMoving disciple-tools-plugin-starter-template.php to '$plugin_path.php'"
mv disciple-tools-plugin-starter-template.php "$plugin_path.php"

echo -e "\nAll replacements done. Destroying this script since it can be harmful if you re-run it."
rm customize.sh

echo -e "\nThanks for choosing Disciple.Tools!"