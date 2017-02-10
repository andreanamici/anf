<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php echo defined("PORTAL_TITLE_EXT") ? PORTAL_TITLE_EXT : $_SERVER['SERVER_NAME'] . ' - Error '; ?></title>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {

                function __maiTo()
                {
                    var message = encodeURIComponent($.trim($("#errorMessageHTML").text()));

                    <?php if (defined("PROJECT_NAME")) { ?>
                                            var subject = encodeURIComponent('<?php echo PROJECT_NAME . ' - Exception '; ?>');
                    <?php } else { ?>
                                            var subject = "Error";
                    <?php } ?>

                    <?php if (defined("EMAIL_ADMIN_LIST")) { ?>
                                            var receivers = "<?php echo str_replace(";", ",", EMAIL_ADMIN_LIST); ?>";
                    <?php } else { ?>
                                            var receivers = "";
                    <?php } ?>

                    var pop = window.open('mailto:' + receivers + '?subject=' + subject + '&body=' + message, '', 'width=1,height=1,top=-100,left=-100,_blank');

                    if (!pop)
                    {
                        alert('Impossibile inviare la mail!!');
                        return false;
                    }

                    window.setTimeout(function () {
                        pop.close();
                    }, 3000);
                    return true;
                }

                <?php if (defined("PORTAL_SOS_AUTOMATIC") && PORTAL_SOS_AUTOMATIC) { ?>
                     __maiTo();
                <?php } else { ?>
                    $("#soslink").click(function (e) {
                        e.preventDefault();
                        __maiTo();
                    });
                <?php } ?>
            });
        </script>
        <link href="https://fonts.googleapis.com/css?family=Cutive+Mono" rel="stylesheet">
    </head>
    <style>
        body{
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }
        #container{
            background: #f4f4f4;
            padding: 10px
        }
        .terminaldisplay{
                font-family: 'Cutive Mono', monospace;
                background: #000;
                color: #e0e0e0;
                padding: 20px;
                border-radius: 10px;
                font-size: 12px;
        }
        h1 {
            color: red;
        }
        .normaldisplay{
            border: 1px solid red;
            color: #000;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px
        }
        .btn-frame{
            background: red;
            color: #fff;
            text-decoration: none;
            font-weight: lighter;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 4px;
        }
    </style>
    <body>
        <div id="container">
            <div id="errorMessageHTML">
                <p>
                    <h1>Errore!</h1>
                    <div class='normaldisplay'>
                        <h3 align="left">Si &egrave; verificato un Errore improvviso nella pagina:&nbsp;&nbsp;http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>.</h3>
                        <p><strong>Device:</strong> <?php echo $_SERVER['HTTP_USER_AGENT']; ?></p>
                        <p><strong>IP:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
                        <p><strong>Exception:</strong>  <?php echo get_class($e); ?></p>
                        <p><strong>Error:</strong>      <?php echo $e->getMessage(); ?></p>
                        <p><strong>Error Code:</strong> <?php echo $e->getCode(); ?></p>
                        <p><strong>File:</strong> <?php echo $_SERVER['PHP_SELF']; ?></p>
                    </div>
                    <div class='terminaldisplay'>
                        <h3>BreakTrace: </h3>
                        <p><?php echo preg_replace("/#/", "<br><br>#", $e->getTraceAsString()); ?></p>
                    </div>
                </p>
            </div>
            <div id="MessageLink">
                <p>
                    <?php if (defined("PORTAL_SOS_AUTOMATIC") && PORTAL_SOS_AUTOMATIC) { ?>
                        <h4>Per segnalare questo problema <a href="#" id="soslink"> clicca qui</a>.
                            <span style='font-size:13px;'><i>(Verrà inviata una mail sfruttando il tuo attuale client di posta elettronica)</i></span>
                        </h4>
                    <?php } ?>
                </p>
            </div>
            <h4><a class='btn-frame' href="javascript:void(0);" onclick="javascript:history.back();">Indietro</a></h4>
        </div>
</html>