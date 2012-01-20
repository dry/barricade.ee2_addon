<div class="section_wrapper">
	<?= form_open($form_url, array('id' => 'save_settings')) ?>
	<table class="mainTable" cellspacing="0">
	<thead>
		<tr>
			<th><?= lang('barricade_settings_option') ?></th>
			<th><?= lang('barricade_settings_setting') ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?= lang('barricade_updater_enabled') ?>
			</td>
			<td>
				<?= form_dropdown('enabled', $enabled_options, $enabled) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?= lang('barricade_updater_access_key') ?>
			</td>
			<td>
				<?= form_input('access_key', $access_key, "size='40'") ?>
			</td>
		</tr>
	</tbody>
	</table>
	<p><?= form_submit(array('name' => 'save_settings'), lang('barricade_save_settings')) ?></p>
	</form>
</div>
