var userDeletePresubmit = document.getElementById('user-delete-presubmit');
var userDeletePassword = document.getElementById('user-delete-password');
var showUserDeletePresubmit = document.getElementById('show-user-delete-presubmit');
var showUserDeleteSubmit = document.getElementById('show-user-delete-submit');
var userDeleteContainer = document.getElementById('user-delete-container');

userDeletePresubmit.addEventListener('click', function() {

	// hide password field and submit button
	showUserDeletePresubmit.style.display = 'none';

	
	// create a new element with a wait time counting down
	let showWaitTime = document.createElement('p');

	showWaitTime.id = 'show-wait-time';
	showWaitTime.classList.add('bold-text', 'red-text');
	showWaitTime.innerHTML = 'Please wait <span id="wait-time">5</span> seconds and make sure you really do want to delete everything';

	userDeleteContainer.appendChild(showWaitTime);

	// start to countdown
	let waitTime = document.getElementById('wait-time');

	function sleep(s) {
		return new Promise(resolve => setTimeout(resolve, s * 1000));
	}
	
	async function count_down() {
	
		while ( waitTime.innerText > 0 )
		{
			await sleep(1);
			waitTime.innerText -= 1;
		}

		// timer is at 0, remove the wait time element
		document.getElementById('show-wait-time').remove();

		// show final confirmation
		showUserDeleteSubmit.style.display = 'block';
	
	}

	count_down();

});

