<html>
    <title>anf - Alternative Framework</title>    
    <script type="text/javascript" id="anf-js-framework">
        <?php echo render_route('_anfjs');?>
    </script>
    <link href="<?php echo resource_url('css/anf.css');?>" rel="stylesheet">
    <body>
        <div class="main-page">
            <h1><i>anf</i> - Alternative Framework</h1>
            <p>
                Hi <?php echo $name;?>! Today is the <?php echo $date;?> and this is the main page of <strong>anf</strong>. Now you have a great power, and from great powers comes great responsibilities.
            </p>
        </div>
    </body>
</html>