<div id="page-wrapper">
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Blank</h1>
        <?php $attributes = array('role' => 'form','name'=>'testing', 'id' => 'testing');?>
        <?php echo form_open('dashboard/testing',$attributes);?>
        
        <label class="text-danger"><?php echo ($this->session->flashdata('gogo'))? $this->session->flashdata('gogo'): '';?></label>
        <label class="text-primary"><?php echo ($fuck) ? $fuck :"";?></label>
        
        <input type="text" name="go" id="go" value="<?php echo set_value('go'); ?>" class="form-control" />
        <?php echo form_error('go');?>
        <button type="submit" class="btn btn-default">GO</button>
        <?php echo form_close();?>
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->
</div>