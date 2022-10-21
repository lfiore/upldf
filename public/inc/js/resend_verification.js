if ( document.getElementById('wait-time') !== null )
{

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

		document.getElementById('exit-message-title').innerText = 'You can now request another email';

		let exitMessages = document.getElementById('exit-messages');

		exitMessages.innerHTML = '';

		let resendMessage = document.createElement('p');
		let resendMessageContent = document.createTextNode('You can now go back to the login page and request another verification email');
		resendMessage.appendChild(resendMessageContent);

		exitMessages.appendChild(resendMessage);

	}

	count_down();

}