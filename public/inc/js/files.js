let fileActionsToggles =  document.getElementsByClassName('file-actions-toggle');

[].forEach.call(fileActionsToggles, function(file) {

	let form_id = file.id + '-form';
	let form = document.getElementById(form_id);
	form.style.display = 'none';

	file.addEventListener('click', function() {

		let form_id = this.id + '-form';
		let form = document.getElementById(form_id);

		if ( form.style.display === 'none' )
		{
			form.style.display = 'block';
		}

		else
		{
			form.style.display = 'none';
		}

	});
});