find ./ -type f -print0 | xargs -0 perl -pi -e 's/Disciple_Tools_Plugin_Starter_Template/Disciple_Tools_Plugin_Starter_Template/g';
find ./ -type f -print0 | xargs -0 perl -pi -e 's/disciple_tools_plugin_starter_template/disciple_tools_plugin_starter_template/g';
find ./ -type f -print0 | xargs -0 perl -pi -e 's/disciple-tools-plugin-starter-template/disciple-tools-plugin-starter-template/g';
find ./ -type f -print0 | xargs -0 perl -pi -e 's/starter_post_type/starter_post_type/g';
find ./ -type f -print0 | xargs -0 perl -pi -e 's/Plugin Starter Template/Plugin Starter Template/g';
mv disciple-tools-plugin-starter-template.php disciple-tools-plugin-starter-template.php
rm .rename.sh