<table style="width: 95%;height: 600px;">
    <tr style="width: 100%">
    <?php foreach ($output['areas'] as $k => $v) { ?>
        <?php if($k >0 && $k%5 == 0) { ?></tr><tr style="width: 100%"><?php } ?>
        <td style="width: 20%; height: 30px;">
            <input type="checkbox" class="J_Province" id="J_Province_<?php echo $v['self_receive_spot_cd']; ?>" value="<?php echo $v['self_receive_spot_cd']; ?>"/>
            <label for="J_Province_<?php echo $v['self_receive_spot_cd']; ?>"><?php echo $v['self_receive_nm']; ?></label>
        </td>
    <?php } ?>
    </tr>
</table>