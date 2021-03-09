## English ##

1. To verify users edit a user and switch to the tab "Account settings". Below you can set a checkbox.

2. Normally, the plugin automatically inserts the variable into the postbit template to display the icon. If this doesn't work for you (maybe because you have your own individual template/theme), 
you can add the following variable to the "postbit" template: {$post['icon_vf']}

3. Normally, the plugin automatically inserts the variable into the member_profile template to display the icon. If this doesn't work for you (maybe because you have your own individual template/theme), 
you can add the following variable to the "member_profile" template: {$icon_vf}

4. Normally, the plugin automatically edit the headerinclude template to activate/include fontawesome. If this doesn't work for you (maybe because you have your own individual template/theme), 
you can add the following to the "headerinclude" template: <link href="inc/plugins/css/all.min.css" rel="stylesheet">
