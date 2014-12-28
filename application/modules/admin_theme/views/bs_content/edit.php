<div id="page-wrapper">

            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h1 class="page-header">
                            Blank Page <small>Subheading</small>
                        </h1>
                        <ol class="breadcrumb">
                            <li>
                                <i class="fa fa-dashboard"></i>  <a href="<?php echo base_url();?>assets/sb/index.html">Dashboard</a>
                            </li>
                            <li class="active">
                                <i class="fa fa-file"></i> Blank Page
                            </li>
                        </ol>
                    </div>
                </div>
                
                
                
                <!-- /.row -->
                <div class="row">
                <div class="col-lg-12">
                    <?php echo form_open('',array('id'=>'insert_test','name'=>'insert_test','role'=>'form'));?>
                        <div class="col-lg-12"><?php echo validation_errors('<div class="text-danger">', '</div>'); ?></div>
                        <div class="form-group col-lg-3">
                        <?php echo form_label('Tên phường xã','name');?>
                        <?php echo form_input(array('name'=> 'name','id'=> 'name','value'=> $gg->ward_name,'class'=> 'form-control'));
                        ?>
                        </div>                        
                        <div class="form-group col-lg-3">
                        <?php echo form_label('Tỉnh thành phố','province');?>
                        <?php
                        $option = 'class="form-control" id="province"';                        
                        echo form_dropdown('type', $province_list,$province_relax,$option);
                        ?>
                        </div>                        
                        <div class="form-group col-lg-3">
                        <?php echo form_label('Quận huyện','district');?>
                        <?php
                        
                        $option = 'class="form-control" id="district"';                    
                        echo form_dropdown('district',$district_list , $district_relax ,$option);
                        ?>
                        </div>                        
                        <div class="form-group col-lg-3">
                        <?php echo form_label('Kiểu phường xã','type');?>
                        <?php 
                        $options = array('Xã'  => 'Xã','Phường'    => 'Phường','Thị Trấn'   => 'Thị Trấn');
                        $option = 'class="form-control"';                        
                        echo form_dropdown('type', $options,$gg->ward_type,$option);
                        ?>                        
                        </div>
                        <div class="form-group col-lg-12">
                        <?php 
                        $data = array('name' => 'button','id' => 'button','value' => 'Insert','type' => 'submit','class' => 'btn btn-default','content' => 'Insert');                        
                        echo form_button($data);
                        ?>
                        <?php 
                        $data = array('name' => 'reset','id' => 'reset','value' => 'Reset','type' => 'reset','class' => 'btn btn-default','content' => 'Reset');                        
                        echo form_button($data);                        
                        ?>
                        </div>                        
                    <?php echo form_close();?>
                </div>
                </div>

            </div>
            <!-- /#page-wrapper -->

        </div>
        <!-- /#wrapper -->