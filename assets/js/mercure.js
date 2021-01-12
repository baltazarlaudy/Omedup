window.onload = () =>{

    let messagerie = document.getElementById('messaging')
    let message_history = document.getElementById('message_history');
    let formPostMessage = document.getElementById('form-post-message');
    const submitButton = document.getElementById('submitFormButton');
    const mesgs = document.getElementById('mesgs');
    const id = messagerie.dataset.id
// loader
    const loader = document.querySelector("#loading");

    // showing loading
    function displayLoading() {
        loader.classList.add("display");
        // to stop loading after some time
    }

    // hiding loading
    function hideLoading() {
        loader.classList.remove("display");
    }

    //fetch to get messase
    async function getData(id) {
        displayLoading()
        let response = await fetch('/private/message/json/'+id)
        let data = await response.json()
        const dataJson = JSON.parse(data)
        hideLoading()
        dataJson.forEach(function (d) {
            if(d.mine == true){
                message_history.insertAdjacentHTML('beforeend','<div class="outgoing_msg" id="outgoing_msg">' +
                    '<div class="sent_msg">\n' +
                    '                            <p>'+d.content+'</p>\n' +
                    '                            <span class="time_date">'+d.createdAt+'</span>\n' +
                    '                        </div></div>')
            }else {
                message_history.insertAdjacentHTML('beforeend','<div class="incoming_msg" id="incomming_msg">' +
                    '<div class="incoming_msg_img">\n' +
                    '                            <img src="https://ptetutorials.com/images/user-profile.png" alt="sunil"></div>\n' +
                    '                        <div class="received_msg">\n' +
                    '                            <div class="received_withd_msg">\n' +
                    '                                <p>'+d.content+'</p>\n' +
                    '                                <span class="time_date">'+d.createdAt+'</span></div>\n' +
                    '                        </div></div>')
            }
        })
        message_history.scrollTop = message_history.scrollHeight
    }

    getData(id)
    // submitButton.disabled = true
    const postMessage = async function (event){
        event.preventDefault()
        const inputText = document.getElementById('inputText')
        const urlData = formPostMessage.action

        //let content = document.querySelector("input[type['text']")
        const formData = new FormData(formPostMessage);
        //formData.append('content', content)
        const headers = new Headers();
        const request = new Request(urlData)
        const response = await fetch(request, {
            method: 'POST',
            headers: headers,
            body: formData
        })
        console.log(inputText.value)
    }

    formPostMessage.addEventListener('submit', postMessage)

    //mercure hub subscriber
    const username = messagerie.dataset.username
    console.log(username)
    const url = new URL('http://localhost:3000/.well-known/mercure');
    url.searchParams.append('topic', '/message/'+id);
// Subscribe to updates of several Book resources

    const eventSource = new EventSource(url, {
        withCredentials: true
    });
    eventSource.onmessage = event => {
        let data = JSON.parse(event.data)
        if(data.user.username != username){
            message_history.insertAdjacentHTML('beforeend','<div class="incoming_msg" id="incomming_msg">' +
                '<div class="incoming_msg_img">\n' +
                '                            <img src="https://ptetutorials.com/images/user-profile.png" alt="sunil"></div>\n' +
                '                        <div class="received_msg">\n' +
                '                            <div class="received_withd_msg">\n' +
                '                                <p>'+data.content+'</p>\n' +
                '                                <span class="time_date"> 11:01 AM | June 9</span></div>\n' +
                '                        </div></div>')
        }else if (data.user.username == username){
            message_history.insertAdjacentHTML('beforeend','<div class="outgoing_msg" id="outgoing_msg">' +
                '<div class="sent_msg">\n' +
                '                            <p>'+data.content+'</p>\n' +
                '                            <span class="time_date"> 11:01 AM | June 9</span>\n' +
                '                        </div></div>')

        }
        message_history.scrollTop = message_history.scrollHeight
    }
    window.addEventListener('beforeunload', function () {
        if(eventSource != null){
            eventSource.close()
        }
    })
}