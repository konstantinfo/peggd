<br />
<script type="text/javascript">
    function CheckCheckboxes(action_to)
    {
        var elLength = document.EventsAdminProcessForm.elements.length;
        document.EventsAdminProcessForm.act.value = action_to;

        var inc = 0;
        var chk_cnt = 0;
        for (i = 0; i < elLength; i++)
        {
            var type = document.EventsAdminProcessForm.elements[i].type;

            if (type == "checkbox")
            {
                inc = inc + 1;
                if (document.EventsAdminProcessForm.elements[i].checked)
                {
                    chk_cnt = chk_cnt + 1;
                }
            }
        }

        if (inc > 0 && chk_cnt > 0)
        {
            if (action_to == 'delete')
            {
                if (confirm('Are you sure you want to delete this record?'))
                    document.EventsAdminProcessForm.submit();
                else
                    return false;
            }
            else
            {
                document.EventsAdminProcessForm.submit();
            }
        }
        else
        {
            if (action_to == 'active')
            {
                alert('Please check the checkbox to active the event(s)');
            }
            else if (action_to == 'deactive')
            {
                alert('Please check the checkbox to deactive the event(s)');
            }
            else if (action_to == 'delete')
            {
                alert('Please check the checkbox to delete the event(s)');
            }

        }
    }
    function checkAll()
    {

        var elLength = document.EventsAdminProcessForm.elements.length;
        if (!document.EventsAdminProcessForm.elements[0].checked)
        {
            for (i = 0; i < elLength; i++)
            {
                document.EventsAdminProcessForm.elements[i].checked = true;
            }
        }
        else
        {
            for (i = 0; i < elLength; i++)
            {
                document.EventsAdminProcessForm.elements[i].checked = false;
            }
        }

    }
</script>
<div class="grid_4"  >
    <div class="panel">
        <h2 class="cap"><?php echo $title_for_layout; ?></h2>
        <div class="content">	
            <div style="float:left;padding-bottom:13px">
                <input type="hidden" name="act" id="act" value="" />
                <input type="hidden" name="redact" id="redact" value="index" />
            </div>	

            <br />
            <br />
            <table cellspacing="1" cellpadding="0" border="0" class="tablesorter styled" id="tablesorter-sample">
                <thead> 
                    <?php
                    $tableHeaders = $this->Html->tableHeaders(array(
                        $this->Paginator->sort('ticket_id', 'S.no'),
                        $this->Paginator->sort('name', 'Event Name'),
                        $this->Paginator->sort('user_name', 'User Name'),
                        $this->Paginator->sort('email', 'Email'),
                        $this->Paginator->sort('ip_address', 'IP Address'),
                        $this->Paginator->sort('purchase_date', 'Purchase Date'),
                    ));
                    echo $tableHeaders;
                    ?>
                </thead> 
                <tbody>
                    <?php
                    $rows = array();
                    if (count($ticketDetails) > 0) {
                        foreach ($ticketDetails AS $ticketDetail) {
                            $fullname = isset($ticketDetail['User']['fullname']) ? $ticketDetail['User']['fullname'] : 'information not available';
                            $username = isset($ticketDetail['User']['username']) ? $ticketDetail['User']['username'] : 'information not available';
                            $actions = '';
                            $actions = "<div style='text-align:center;'>" . $actions . '</div>';

                            $actionsArr = array($actions, array('style' => 'min-width:150px;max-width:150px;width:150px;'));

                            $rows[] = array(
                                $ticketDetail['TicketDetail']['id'],
                                $ticketDetail['Event']['name'],
                                $fullname,
                                $username,
                                $ticketDetail['TicketDetail']['ip_address'],
                                $ticketDetail['TicketDetail']['created'],
                            );
                        }
                    }
                    echo $this->Html->tableCells($rows);
                    ?>
                </tbody>
            </table>
            <?php //echo $this->Form->end(); ?>
            <div class="pager" id="table-pager-1" style="height:30px;">
                <div style="float:left;">
                    <?php
                    if (isset($ticketDetails[0]['TicketDetail']['id']) && $ticketDetails[0]['TicketDetail']['id'] != '') {
                        $userId = $ticketDetails[0]['TicketDetail']['id'];
                    } else {
                        $userId = '';
                    } echo $this->Form->create('Event', array('url' => array('controller' => 'events', 'action' => 'tracklisting', 504), 'type' => 'post', 'div' => '', 'class' => ''));
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