<link rel='stylesheet' href='<?php echo resource_url('css/style.css');?>' media='all'/>
<?php foreach(flash_get_all_messages() as $type => $message){ ?>
    <div class="flash-messages <?php echo $type;?>"><?php echo $message; ?></div>
<?php } ?>