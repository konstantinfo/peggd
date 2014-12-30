<br />
<br />
<br />
<script type="text/javascript">
    function CheckCheckboxes(action_to)
    {
        var elLength = document.UsersAdminProcessForm.elements.length;
        document.UsersAdminProcessForm.act.value=action_to;
	
        var inc=0;
        var chk_cnt=0;
        for (i=0; i<elLength; i++)
        {
            var type = document.UsersAdminProcessForm.elements[i].type;
		
            if (type=="checkbox") 
            {
                inc=inc+1;
                if (document.UsersAdminProcessForm.elements[i].checked)
                {
                    chk_cnt	=	chk_cnt	+	1;
                }
            }
        }
	
        if(inc>0 && chk_cnt>0)
        {
            if(action_to=='delete')
            {
                if(confirm('Are you sure you want to delete this record?'))
                    document.UsersAdminProcessForm.submit();
                else
                    return false;
            }
            else
            {
                document.UsersAdminProcessForm.submit();
            }
        }
        else
        {
            if(action_to=='active')
            {
                alert('Please check the checkbox to active the user(s)');
            }
            else if(action_to=='deactive')
            {
                alert('Please check the checkbox to deactive the user(s)');
            }
            else if(action_to=='delete')
            {
                alert('Please check the checkbox to delete the user(s)');
            }
		
        }
    }
    function checkAll()
    {
	
        var elLength = document.UsersAdminProcessForm.elements.length;
        if(!document.UsersAdminProcessForm.elements[0].checked)
        {
            for (i=0; i<elLength; i++)
            {
                document.UsersAdminProcessForm.elements[i].checked=true;
            }
        }
        else
        {
            for (i=0; i<elLength; i++)
            {
                document.UsersAdminProcessForm.elements[i].checked=false;
            }
        }
	
    }
</script>
<div class="grid_4"  >
    <div class="panel">
        <h2 class="cap"><?php echo $title_for_layout; ?></h2>
        <div class="content">	

            <div class="pager" style=" text-align: left;width:100%;">
                <?php echo $this->Html->link(__('All User (' . $all_users . ')', true), array('action' => 'index'), array('class' => 'button medium')); ?>
                <?php echo $this->Html->link(__('Active Users  (' . $active_users . ')', true), array('action' => 'active'), array('class' => 'button medium ')); ?>
                <?php echo $this->Html->link(__('Deactive User  (' . $deactive_users . ')', true), array('action' => 'deactive'), array('class' => 'button medium')); ?>
                <?php echo $this->Html->link(__('New User', true), array('action' => 'add'), array('class' => 'button medium yellow')); ?>                
            </div>

            <div style="float:right;height:30px">
                <?php
                echo $this->Form->create('search', array('name' => 'search', 'url' => array('controller' => 'users', 'action' => 'search'), 'class' => 'styled'));
                ?>
                <table cellspacing="1" cellpadding="0" border="0" class=" " id="">
                    <tr>
                        <td  style="padding-right:10px;">
                            <?php
                            echo $this->Form->text('keyword', array('label' => false, 'div' => '', 'type' => 'text', 'class' => 'textbox', 'style' => 'text-align: left;height:25px; font-size: 16px;'));
                            ?>
                        </td>
                        <td>
                            <a class="button medium red" onclick="document.search.submit()" href="#">Search</a>
                        </td>
                    </tr>
                </table>
                <?php
                echo $this->Form->end();
                ?>
            </div>

            <?php echo $this->Form->create('Users', array('name' => 'UsersAdminProcessForm', 'url' => array('controller' => 'users', 'action' => 'process'))); ?>
            <div style="float:left;padding-bottom:13px">
                <input type="hidden" name="act" id="act" value="" />
                <input type="hidden" name="redact" id="redact" value="index" />

                <?php
                if (count($users) > 0) {
                    ?>
                    <table cellspacing="1" cellpadding="0" border="0" class=" " id="">
                        <tr>		
                            <td  style="padding-right:10px;">
                                <select name="selectBox1" class="choosen" id="selectBox1"style='height:25px;width:150px;padding-top:2px'    >
                                    <option value="active">Active</option>
                                    <option value="deactive">Deactivate</option>
                                    <option value="delete">Delete</option>
                                </select> 
                            </td>
                            <td>
                                <input class="button red small" type="button" onclick="CheckCheckboxes($('#selectBox1').val())" value="Apply" />
                            </td>
                        </tr>
                    </table>	

                    <?php
                }
                ?>
            </div>	

            <br />
            <br />
            <table cellspacing="1" cellpadding="0" border="0" class="tablesorter styled" id="tablesorter-sample">
                <thead> 
                    <?php
                    $tableHeaders = $this->Html->tableHeaders(array(
                                __('<input class="checkall" onclick="checkAll()"  type="checkbox">', true),
                                __('Image', true),
                                $this->Paginator->sort('fullname','Merchant Name'),
                                $this->Paginator->sort('username','Username'),
                                $this->Paginator->sort('status', 'Status'),
                                __('Actions', true, array('class' => "options-row")),
                            ));
                    echo $tableHeaders;
                    ?>
                </thead> 
                <tbody>
                    <?php
                    $rows = array();
                    foreach ($users AS $user) {
                        $actions = '';

                        if ($user['User']['id'] != 1) {

                            $actions = $this->Html->link(__('Edit', true), array('controller' => 'users', 'action' => 'edit', $user['User']['id']), array('class' => "icon-button edit", 'alt' => 'Edit', 'title' => 'Edit'));
                            $actions .= ' ';
                            $actions .= $this->Html->link(__('Change Password', true), array('controller' => 'users', 'action' => 'reset_password', $user['User']['id']), array('class' => "icon-button password", 'alt' => 'Change Password', 'title' => 'Change Password'));
                            $actions .= ' ';

                            $actions .= ' ' . $this->Html->link(__('Delete', true), array(
                                        'controller' => 'users',
                                        'action' => 'delete',
                                        $user['User']['id'],
                                            ), array('class' => "icon-button delete", 'alt' => 'Delete', 'title' => 'Delete'), __(' Are you sure you want to delete this record?', true)
                            );
                        } elseif ($this->Session->read('Auth.User.id') == 1) {
                            $actions = $this->Html->link(__('Edit', true), array('controller' => 'users', 'action' => 'edit', $user['User']['id']), array('class' => "icon-button edit", 'alt' => 'Edit', 'title' => 'Edit'));
                            $actions .= ' ';
                            if ($user['User']['id'] != 1) {
                                $actions .= $this->Html->link(__('Change Password', true), array('controller' => 'users', 'action' => 'reset_password', $user['User']['id']), array('class' => "icon-button password", 'alt' => 'Change Password', 'title' => 'Change Password'));
                                $actions .= ' ';
                            }
                        }

                        $chkbox = '';
                        if ($user['User']['id'] != 1)
                            $chkbox = $this->Form->checkbox('Users.' . $user['User']['id'] . '.id', array('class' => 'checkbox2', 'style' => 'float:center'));
                        if ($user['User']['status'] == 1)
                            $is_active = '<img title="Active" src="' . Configure::read('Site.url') . 'images/alert_icon_confirm.png">';
                        else
                            $is_active = '<img title="Deactive" src="' . Configure::read('Site.url') . 'images/alert_icon_close.png">';

                        $is_active = "<div style='text-align:center;'>" . $is_active . '</div>';
                        $chkbox = "<div style='text-align:center;'>" . $chkbox . '</div>';
                        $actions = "<div style='text-align:center;'>" . $actions . '</div>';

                        $actionsArr = array($actions, array('style' => 'min-width:150px;max-width:150px;width:150px;'));
                       $imageURL = $this->function->getUploadImage($user['User']['image'],'users',50,50);
                       $imageFullURL = $this->function->getUploadImage($user['User']['image'],'users',500,500,0,0);
                        $image= '<a href="' . $imageFullURL  . '" class="fancybox" rel="gallery_group" title = "User image"><img alt="User image" src="' . $imageURL . '"></a>';
                        
                        $rows[] = array(
                            $chkbox,
                            $image,
                            $user['User']['fullname'],
                            $user['User']['username'],
                            $is_active,
                            $actionsArr,
                        );
                    }
                    echo $this->Html->tableCells($rows);
                    ?>
                </tbody>
            </table>
            <?php echo $this->Form->end(); ?>
            <div class="pager" id="table-pager-1" style="height:30px;">
                <div style="float:left;">
                    <?php
                    echo $this->Form->create('User', array('action' => 'index', 'controller' => 'users',
                        'type' => 'post', 'div' => '', 'class' => ''));
                    ?>
                    <?php
                    echo $this->Form->input('order_query', array('label' => '',
                        'options' => array('10' => '10', '20' => '20', '30' => '30', '40' => '40',
                            '50' => '50', '60' => '60', '70' => '70', '80' => '80', '90' => '90',
                            '100' => '100'), 'div' => '', 'class' => 'pagesize', 'style' => 'width:100px', 'onchange' => 'form.submit();')
                    );
                    ?>
                    <?php echo $this->Form->end(); ?>
                </div>


                <div style="float:right;">
                    <table cellpadding="0" cellspacing="0" border="0" >
                        <tr>
                            <td>
                                <?php echo $this->Paginator->first($this->Html->image('/images/table_pager_first.png', array('alt' => __('previous', true), 'border' => 0)), array('escape' => false, 'class' => 'button small green first')); ?>
                                <?php
                                echo $this->Paginator->prev($this->Html->image('/images/table_pager_previous.png', array('alt' => __('previous', true), 'border' => 0)), array('escape' => false, 'class' => 'button small green prev'));
                                ?>	
                            </td><td valign="middle" style="vertical-align:middle"><?php echo $this->Paginator->numbers(array('class' => 'blue', 'style' => 'padding-top:20px')); ?></td>
                            <td>
                                <?php
                                echo $this->Paginator->next($this->Html->image('/images/table_pager_next.png', array('alt' => __('previous', true), 'border' => 0)), array('escape' => false, 'class' => 'button small green next'));
                                ?>	
                                <?php
                                echo $this->Paginator->last($this->Html->image('/images/table_pager_last.png', array('alt' => __('previous', true), 'border' => 0)), array('escape' => false, 'class' => 'button small green last'));
                                ?>	
                            </td>
                        </tr>
                    </table>
                </div>			

            </div>
            <div class="counter" style="text-align:center"><?php echo $this->Paginator->counter(); ?></div>		
        </div>
    </div>
</div>
<div class="grid_1">
</div>



