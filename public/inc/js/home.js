var uploadFile = document.getElementById('upload-file');
var uploadSubmit = document.getElementById('upload-submit');
var selectedFile = document.getElementById('selected-file');

uploadFile.addEventListener('change', function() {

	while ( selectedFile.firstChild )
	{
		selectedFile.removeChild(selectedFile.firstChild);
	}

	if ( document.getElementById('upload-file').files.length !== 0 )
	{
		var file = document.getElementById('upload-file').files[0];
	}
	else
	{
		uploadSubmit.disabled = true;
		return;
	}

	if ( file.size <= document.getElementById('max-file-size').value )
	{
		uploadSubmit.disabled = false;
		selectedFile.classList.remove('errors');
		var error = '';
	}
	else
	{
		uploadSubmit.disabled = true;
		selectedFile.classList.add('errors');
		var error = ' (this file is too large)';
	}

	let file_info = document.createTextNode(file.name + error);

	selectedFile.appendChild(file_info);

});


document.getElementById('select-file-button').addEventListener('click', function() {
	uploadFile.click();
});

uploadSubmit.addEventListener('click', function() {

	// submit form
	document.getElementById('upload-form').submit();

	// disable submit button
	this.disabled = true;

	// remove form and display an "uploading" message
	let uploadFormBox = document.getElementById('upload-form-box');

	uploadFormBox.innerHTML = '';

	let uploadMessage = document.createElement('p');
	let uploadMessageContent = document.createTextNode('Your file is now uploading');
	uploadMessage.appendChild(uploadMessageContent);

	uploadMessage.classList.add('bold-text');

	uploadFormBox.appendChild(uploadMessage);

});