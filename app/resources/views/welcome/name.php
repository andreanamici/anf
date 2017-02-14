<html>
    <title>Welcome name</title>  
    <script>
        <?php echo render_route('_anfjs');?>
    </script>
    <body>
        <?php echo render_view('welcome/messages');?>
        <p>
            Hi <strong><?php echo $name;?>!</strong>
            <br />
            This this page is located at <?php echo __FILE__;?>
        </p>
        <?php echo form_open();?>
            <?php echo form_label('do you want to greet someone?', 'input-name');?>
            <?php echo form_input(array('name' => 'name','id' => 'input-name','placeholder' => 'Type name here...'));?>
        <?php echo form_close();?>
        
        <a href="<?php echo path($currentRoute, ['name' => $name]);?>"><?php echo translate('WELCOME_NAME_GO_LINK');?></a>
    </body>
</html>