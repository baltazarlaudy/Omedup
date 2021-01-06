let form = document.querySelector('#form_message')
    form.addEventListener('submit', postData);

async function postData (e){
    e.preventDefault()
    const url = form.action
    const headers = new Headers()
    const formData = new FormData(form)
    let request = new Request(url, {
        method: 'POST',
        headers: headers,
        body: formData
    })

    const response = await fetch(request)
    console.log(response)
}
let message = document.getElementById('messages')
let id = message.dataset.id
let userId = message.dataset.username

console.log(id)

// URL is a built-in JavaScript class to manipulate URLs
const url = new URL('http://localhost:3000/.well-known/mercure');
url.searchParams.append('topic', '/conversation/'+id);
url.searchParams.append('topic', '/message/'+userId);
// Subscribe to updates of several Book resources
// All Review resources will match this pattern


const eventSource = new EventSource(url,{
    withCredentials: true
});
eventSource.onmessage = event => {
    console.log(event);
}
