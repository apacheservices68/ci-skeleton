<div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                            </div>
                            <!-- /input-group -->
                        </li>
                        <li>
                            <a href="<?= site_url('dashboard')?>"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                        </li>
                        <li>
                            <a href="<?= site_url('auth')?>"><i class="fa fa-user fa-fw"></i> Users and Groups</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-cog fa-fw"></i> Logs<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li>
                                    <a href="<?= site_url('dashboard/visiter')?>">Log ip address</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/logs')?>">Logs_file</a>
                                </li>                                
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        
                        <li>
                            <a href="#"><i class="fa fa-cog fa-fw"></i> Configuration<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li>
                                    <a href="#">Global config</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/configs')?>">Site setting</a>
                                </li>                                
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-cog fa-fw"></i> Config languages<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li>
                                    <a href="<?= site_url('dashboard/get_language')?>">Language file name</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/languages')?>">Languages</a>
                                </li>                            
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-wrench fa-fw"></i> IP <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li>
                                    <a href="<?= site_url('dashboard/check_ip')?>">Look up ip</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/ip_address')?>">Blocking ip address</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/ip_remove')?>">Bỏ chặn</a>
                                </li>                                
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        
                        <li>
                            <a href="#"><i class="fa fa-wrench fa-fw"></i> Application<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li>
                                    <a href="<?= site_url('dashboard/email_sender')?>">Email sender</a>
                                </li>                                 
                                <li>
                                    <a href="<?= site_url('dashboard/clear_cache')?>">Clear Cache</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/plugins')?>">Plugins</a>
                                </li>                           
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-sitemap fa-fw"></i> Sites<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li>
                                    <a href="<?= site_url('dashboard/categories')?>">Categories</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/products')?>">Products</a>
                                </li>
                               
                                
                                <li>
                                    <a href="#">News <span class="fa arrow"></span></a>
                                    <ul class="nav nav-third-level collapse">
                                        <li>
                                            <a href="<?= site_url('dashboard/leech')?>">Leech Links</a>
                                        </li>
                                        <li>
                                            <a href="<?= site_url('dashboard/news')?>">News</a>
                                        </li>
                                    </ul>
                                    <!-- /.nav-third-level -->
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-book fa-fw"></i> General<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li>
                                    <a href="<?= site_url('dashboard/assets')?>">Assets</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/abouts')?>">Abouts</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/contacts')?>">Contacts</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/counters')?>">Counter</a>
                                </li>                             
                                <li>
                                    <a href="<?= site_url('dashboard/comments')?>">Comments</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/socials')?>">Social</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/images')?>">Images</a>
                                </li>
                                <li>
                                    <a href="<?= site_url('dashboard/reservations')?>">Reservation</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                    </ul>
                </div>