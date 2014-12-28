<div class="container">
<div class="row">
<div class="col-md-5 col-md-offset-4">
<div class="login-panel panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">Please Sign In</h3>
</div>
<div class="panel-body">
    <p><?php echo lang('login_subheading'); ?></p>

    <?php if(!empty($message)) echo '<div id="infoMessage" class="alert alert-warning" role="alert"><i class="fa fa-warning"></i> '.$message.'</div>';?>

    <?php echo form_open("auth/login", array('class' => 'form-horizontal')); ?>
    <?php $error = (isset($field['name'])) ? form_error($field['name']) : NULL; ?>
    <div class="control-group <? if (!empty($error)): ?>error<? endif; ?>">
        <div class="form-group">
            <div class="col-sm-4"><?php echo form_label($this->lang->line('login_identity_label'), 'identity', array('class' => 'col-sm-2 control-label')); ?></div>
            <div class="col-sm-8"><?php echo form_input($identity, NULL, 'class="form-control"'); ?></div>
        </div>

        <div class="form-group">
            <div class="col-sm-4"><?php echo form_label($this->lang->line('login_password_label'), 'password', array('class' => 'col-sm-2 control-label')); ?></div>
            <div class="col-sm-8"><?php echo form_input($password, NULL, 'class="form-control"'); ?></div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8">
            <div class="checkbox">
            <label>
                <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?> <?php echo $this->lang->line('login_remember_label'); ?>
            </label>
            </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-12"><?php echo form_submit(array('class' => 'btn btn-lg btn-success btn-block', 'name' => 'login', 'value' => 'Login')); ?></div>
        </div>
    </div>
    <?php echo form_close(); ?>

    <p><a href="forgot_password"><?php echo lang('login_forgot_password'); ?></a></p>
</div>
</div>
</div>
</div>
</div>


