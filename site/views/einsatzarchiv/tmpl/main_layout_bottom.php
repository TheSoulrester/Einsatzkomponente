<?php defined('_JEXEC') or die; ?>

<tfoot>
  <!--Prüfen, ob Pagination angezeigt werden soll-->
  <?php if ($this->params->get('display_home_pagination')) : ?>
    <tr>
      <td colspan="<?php echo $eiko_col; ?>">
        <form action="#" method=post>
          <?php echo $this->pagination->getListFooter(); ?><!--Pagination anzeigen-->
        </form>
      </td>
    </tr>
  <?php endif;?><!--Prüfen, ob Pagination angezeigt werden soll   ENDE -->

  <?php if (!$this->params->get('eiko')) : ?>
    <tr><!-- Bitte das Copyright nicht entfernen. Danke. -->
      <td colspan="<?php echo $eiko_col; ?>">
        <span class="copyright">Einsatzkomponente V<?php echo $this->version; ?>  (C) 2017 by Ralf Meyer ( <a class="copyright_link" href="https://einsatzkomponente.de" target="_blank">www.einsatzkomponente.de</a> )</span>
      </td>
    </tr>
  <?php endif; ?>
</tfoot>

<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
<?php echo JHtml::_('form.token'); ?>

</form>

<?php if ($this->params->get('display_home_map')) : ?>
  <tr>
    <td colspan="<?php echo $eiko_col;?>" class="eiko_td_gmap_main_1">
      <h4>Einsatzgebiet</h4>
      <?php if ($this->params->get('gmap_action','0') == '1') :?>
        <div id="map-canvas" style="width:100%; height:<?php echo $this->params->get('home_map_height','300px');?>;">
          <noscript>Dieser Teil der Seite erfordert die JavaScript Unterstützung Ihres Browsers!</noscript>
        </div>
      <?php endif;?>
      <?php if ($this->params->get('gmap_action','0') == '2') :?>
        <body onLoad="drawmap();">
        <!--<div id="descriptionToggle" onClick="toggleInfo()">Informationen zur Karte anzeigen</div>
        <div id="description" class="">Einsatzkarte</div>-->
        <div id="map" style="width:100%; height:<?php echo $this->params->get('home_map_height','300px');?>;">
          <noscript>Dieser Teil der Seite erfordert die JavaScript Unterstützung Ihres Browsers!</noscript>
        </div>
      <?php endif;?>
    </td>
  </tr>
<?php endif;?>

</table>

<?php echo '<span class="mobile_hide_320">'.$this->modulepos_1.'</span>'; ?>

<script type="text/javascript">
jQuery(document).ready(function () {
    jQuery('.delete-button').click(deleteItem);
});

function deleteItem() {
    var item_id = jQuery(this).attr('data-item-id');
    if (confirm("<?php echo JText::_('COM_EINSATZKOMPONENTE_WIRKLICH_LOESCHEN'); ?>")) {
        window.location.href = '<?php echo JRoute::_('index.php?option=com_einsatzkomponente&task=einsatzberichtform.remove&id=', false, 2) ?>' + item_id;
    }
}
</script>
