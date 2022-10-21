if ( document.getElementById('download-password') !== null )
{
	let downloadPassword = document.getElementById('download-password');
	let downloadSubmit = document.getElementById('download-submit');

	downloadPassword.addEventListener('keyup', function() {

		if ( downloadPassword.value.length > 0 )
		{

			let waitTime = document.getElementById('wait-time');

			if ( waitTime === null )
			{
				downloadSubmit.disabled = false;
			}

			else if ( waitTime.innerText == 0 )
			{
				downloadSubmit.disabled = false;
			}

		}

		else
		{
			downloadSubmit.disabled = true;
		}

	});

}

if ( document.getElementById('wait-time') !== null )
{

	let waitTime = document.getElementById('wait-time');
	let downloadSubmit = document.getElementById('download-submit');
	
	function sleep(s) {
		return new Promise(resolve => setTimeout(resolve, s * 1000));
	}

	async function count_down() {

		while ( waitTime.innerText > 0 )
		{
			await sleep(1);
			waitTime.innerText -= 1;
		}

		// remove wait message
		document.getElementById('wait-message').style.display = 'none';

		// if the file has a password, make sure a password has been entered before enabling the download button
		if ( document.getElementById('download-password') !== null )
		{

			let downloadPassword = document.getElementById('download-password').value;

			// password has been entered
			if ( downloadPassword.length > 0 )
			{
				// enabled button
				downloadSubmit.disabled = false;
			}

		}

		// no password, just enable the button
		else
		{
			downloadSubmit.disabled = false;
		}

	}

	count_down();

}

if ( document.getElementById('download-password') === null && document.getElementById('wait-time') === null  )
{
	document.getElementById('download-submit').disabled = false;
}

document.getElementById('download-submit').addEventListener('click', function() {

	// submit form
	document.getElementById('download-form').submit();

	// disable submit button
	this.disabled = true;

	// remove form and display a message to the user
	let downloadFormBox = document.getElementById('download-form-box');

	downloadFormBox.innerHTML = '';

	let downloadMessage = document.createElement('p');
	let downloadMessageContent = document.createTextNode('Your file is now downloading');
	downloadMessage.appendChild(downloadMessageContent);

	downloadMessage.classList.add('bold-text');

	downloadFormBox.appendChild(downloadMessage);

	// increase downloads
	let fileDownloads = document.getElementById('file-downloads');
	fileDownloads.innerText = parseInt(fileDownloads.innerText, 10) + 1;

});