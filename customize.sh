#!/bin/bash


plugin_name="Plugin Starter Template"
plugin_class="Disciple_Tools_Plugin_Starter_Template"
plugin_function="disciple_tools_plugin_starter_template"
plugin_path="disciple-tools-plugin-starter-template"
plugin_post_type="starter_post_type"


echo -e "\nReplacing string 'Plugin Starter Template'... to '$plugin_name'."
grep -rl "Plugin Starter Template" | grep -Ev customize.sh | xargs sed -i -- "s/Plugin Starter Template/$plugin_name/g"
#sed -i -- "s/Plugin Starter Template/$plugin_name/g" *.php

echo -e "Replacing string 'Disciple_Tools_Plugin_Starter_Template'... to '$plugin_class'."
grep -rl "Disciple_Tools_Plugin_Starter_Template" | grep -Ev customize.sh | xargs sed -i -- "s/Disciple_Tools_Plugin_Starter_Template/$plugin_class/g"


echo -e "Replacing string 'disciple_tools_plugin_starter_template'... to '$plugin_function'."
grep -rl "disciple_tools_plugin_starter_template" | grep -Ev customize.sh | xargs sed -i -- "s/disciple_tools_plugin_starter_template/$plugin_function/g"

echo -e "Replacing string 'disciple-tools-plugin-starter-template'... to '$plugin_path'."
grep -rl "disciple-tools-plugin-starter-template" | grep -Ev customize.sh | xargs sed -i -- "s/disciple-tools-plugin-starter-template/$plugin_path/g"

echo -e "Replacing string 'starter_post_type'... to '$plugin_post_type'."
grep -rl "starter_post_type" | grep -Ev customize.sh | xargs sed -i -- "s/starter_post_type/$plugin_post_type/g"

mv disciple-tools-plugin-starter-template.php "$plugin_path.php"

echo -e "\n\nDone! Thanks for choosing Disciple.Tools."