<div>
    <table border="0" border-color="red" cellpadding="5" cellspacing="5" style="background-color: <?php echo !empty($data['background_color']) ? $data['background_color'] : '' ?>;color:<?php echo !empty( $data['font_color'] ) ? $data['font_color'] : '' ?>;  font-family:<?php echo !empty( $data['font1'] ) ? $data['font1'] : '' ?>;">
        <tr>
            <td style=" width:75%; border-right: 10px solid <?php echo !empty($data['border_color']) ? $data['border_color'] : '' ?>;">
                <table>
                    <tr>
                        <br>
                        <th align="center"><span style="font-size: 36pt; text-transform: uppercase; font-weight:bold;"><?php echo $data['event_title']; ?></span>
                        </th>
                    </tr>
                    
                    <tr><td align="center"><span style="font-size: 16pt;"><?php echo strtoupper( date("jS F, Y", $data['date_time']) ); ?></span> 
                            <span style="font-size: 16pt;">
                                <?php
                                    if ( $data['hide_start_time'] && ! $data['hide_end_time'] ) {
                                        echo "till " . $data['end_time'];
                                    } else if ( $data['hide_end_time'] && ! $data['hide_start_time'] ) {
                                        echo "at " . $data['start_time'];   
                                    } else if ( ! $data['hide_end_time'] && ! $data['hide_start_time'] ) {
                                        echo $data['start_time'] . " To " . $data['end_time']; 
                                    }
                                ?>
                            </span>
                            <br/>
                            <br/>
                        </td></tr>
                    <tr>
                        <td width="20%" align="center" style="<?php echo empty($data['ticket_logo1']) ? 'display: none;' : ''; ?>">
                            <?php if (!empty($data['ticket_logo1'])): ?>
                                <img style="width: 180px; margin: 0px 10px;" src="<?php echo $data['ticket_logo1']; ?>">
                            <?php endif; ?>
                        </td>
                        <td width="<?php echo empty($data['ticket_logo1']) ? '100%' : '60%'; ?>" align="center"> 
                            <br/>
                            <?php if (!empty($data['venue_name'])): ?> <span style="font-size: 16pt; text-transform: uppercase;"><?php echo $data['venue_name']; ?></span>
                                <br/>
                            <?php endif; ?>
                            <?php if (!empty($data['venue_address'])): ?> <span style="font-size: 12pt;"><?php echo $data['venue_address']; ?></span> <br/>
                              
                            <?php endif; ?>
                            <br/>
                            <?php if (!empty($data['organiser'])): ?> <span style=" font-size: 16pt;text-transform: uppercase;"><?php echo $data['organiser']; ?></span>
                            <?php endif; ?>
                            <br/>
                            <br/>
                            <br/>
                            <?php if (!empty($data['age_group'])): ?> <span style="font-size: 16pt; text-transform: capitalize; ">Age Group : <?php echo $data['age_group']; ?></span>
                            <br/>
                            <br/>
                            <br/>
                            <?php endif; ?>
                        </td>
                        </tr><tr><td width="75%" align="left"><span style="font-size:10pt" ><?php echo $data['ticket_price_dec']; ?></span></td><td width="25%" align="left"><span style="font-size: 16pt; text-transform: uppercase;">Price</span><br><span style="font-size: 22pt; text-transform: uppercase; font-weight: bold; font-family: dejavusans;"><?php echo $data['ticket_price']; ?></span></td></tr><br><tr><td width="75%" align="left"><span style="font-size: 16pt;"> <?php echo $data['audience_note']; ?></span></td>
                        <td width="25%" align="left">
                                <?php if(!empty($data['price_option_name'])){?>
                                    <span style="font-size:18pt"><?php echo $data['price_option_name']; ?></span>
                                <?php }?>
                        </td>
                    </tr>
                </table>
                <br>
            </td>
            <td style=" width:25%;" align="center"> 
                <?php if( $data['seat_no'] !== '' ) {?>
                    <br/>
                    <br/>
                    <span style="text-transform: uppercase; font-size: 24pt;"><?php echo $data['seat_type']; ?></span>
                    <br>
                    <br>
                    <span style="font-size: 30pt; font-weight: bold;  text-align:center;"><?php echo $data['seat_no']; ?> </span><?php
                }?>
                <br/>
                <br>
                <span style="font-size: 22pt; font-family: monospace !important;">ID #<?php echo $data['booking_id']; ?></span>
                <br/>
                <br/>
                <span class="kf-event-attr difl">
                    <div class="ep-qrcode-details"  style="width:100%; text-align: center">
                        <img src="<?php echo esc_url( $data['qrcode_image'] ); ?>" width="95" height="95" alt="<?php echo __('QR Code', 'eventprime-event-calendar-management'); ?>" />
                    </div>
                </span>
            </td>
        </tr>
    </table>
</div>
