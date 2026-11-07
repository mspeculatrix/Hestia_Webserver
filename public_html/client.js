/**
 * JavaScript for WebSockets client
 *
 * NB: There are lots of place where we use: wsState = wsSocketState()
 * This is because it's a good idea for the code to always be working with the
 * most up to date value. We check and update the state regularly with
 * startStateChecking(), but that's mainly to update things even when we're
 * doing nothing (eg, in case the connection drops).
 */

const ws_ip = '10.0.150.150'
const ws_port = '8080'
const ws_route = ''
const checkStateInterval = 1000
const buttonStatePause = 1000
let connectBtn

let recvMsg = ''
let consoleEl
let terminal
// let checkIntervalID
let wsState = WebSocket.CLOSED
let prevWSstate = WebSocket.CLOSED

function clickBtn(btnEl, label) {
    const defLabel = btnEl.innerText
    btnEl.classList.add('pressed')
    btnEl.innerHTML = label
    setTimeout(() => {
        btnEl.innerHTML = defLabel
        btnEl.classList.remove('pressed')
    }, buttonStatePause)
}

// Callback for wsSocketConnect()
function receiveMsg(event) {
    console.log(event.data)
    // We're always expecting a JSON string. Let's turn it into an object
    recvMsg = JSON.parse(event.data)
    // Currently we're just printing the message to the terminal. But this
    // would also be the place to take action on received data/commands
    if (terminal && recvMsg) {
        if (Object.keys(recvMsg).length > 0) {
            for (const key in recvMsg) {
                terminal.print(`${key}: ${recvMsg[key]}`)
            }
        }
    }
}

function setConnectButton(state) {
    wsState = wsSocketState()
    switch (wsState) {
        case WebSocket.CONNECTING:
            connectBtn.classList.add('active')
            connectBtn.innerHTML = 'connecting'
            break
        case WebSocket.OPEN:
            connectBtn.classList.add('active')
            connectBtn.innerHTML = 'disconnect'
            break
        case WebSocket.CLOSING:
            connectBtn.classList.add('active')
            connectBtn.innerHTML = 'disconnecting'
            break
        case WebSocket.CLOSED:
            connectBtn.classList.remove('active')
            connectBtn.innerHTML = 'connect'
            break
    }
}

function startStateChecking() {
    const checkID = setInterval(() => {
        wsState = wsSocketState()
        if (wsState !== prevWSstate) {
            // State has changed
            prevWSstate = wsState   // reset
            switch (wsState) {
                case WebSocket.CONNECTING:
                    break
                case WebSocket.OPEN:
                    setConnectButton()
                    terminal.print('Connected')
                    break
                case WebSocket.CLOSING:
                case WebSocket.CLOSED:
                    setConnectButton()
                    terminal.print('Closed')
                    break
            }
        }
    }, checkStateInterval)
    return checkID // Not currently used - for future use
}

function sendMessage(msg) {
    console.log(msg)
    wsState = wsSocketState()
    if (wsState === WebSocket.OPEN) {
        terminal.print('Sending: ')
        terminal.print(msg + ' - ')
        let sent = wsSocketSend(msg)
        if (sent) {
            terminal.print('Sent!')
        } else {
            terminal.print('Send failed!')
        }
    } else {
        terminal.print('No connection!')
    }
}

window.addEventListener('DOMContentLoaded', () => {
    connectBtn = document.getElementById('wsConnect')
    const statusBtn = document.getElementById('statusBtn')
    const messageText = document.getElementById('wsSendMsg')
    consoleEl = document.getElementById('console')

    // Helper function to wire up a button group with exclusive 'active' states
    const setupButtonGroup = (ids) => {
        // Map the IDs to actual DOM elements and filter out any missing ones
        const buttons = ids.map(id => document.getElementById(id)).filter(Boolean)

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove 'active' from all buttons in this specific group
                buttons.forEach(b => b.classList.remove('active'))

                // Add 'active' to the clicked button
                btn.classList.add('active')

                // Send the message using the button's ID
                sendMessage(btn.id)
            });
        });
    };

    // Movement buttons
    setupButtonGroup([
        'DIR_STOP',
        'FWD_STR',
        'FWD_TURN_L',
        'FWD_TURN_R',
        'REV_STR',
        'REV_TURN_L',
        'REV_TURN_R',
        'ROTATE_L',
        'ROTATE_R'
    ])

    // Speed buttons
    setupButtonGroup([
        'SP_CRAWL', 'SP_SLOW', 'SP_NORMAL', 'SP_FAST', 'SP_MAX'
    ])


    // Assign to outer variable
    terminal = new Console(consoleEl)

    // Send contents of message input field when <return> pressed
    messageText.addEventListener('keyup', (event) => {
        event.preventDefault();
        if (event.code === 'Enter') {
            sendMessage(messageText.value)
        }
    })

    connectBtn.addEventListener('click', () => {
        // Update wsState to make sure we're up to date
        wsState = wsSocketState()
        switch (wsState) {
            case WebSocket.CONNECTING:
                terminal.print('Connection in progress...')
                break
            case WebSocket.OPEN:
                // Currently connected, so disconnect
                terminal.print('Disconnecting... ')
                wsSocketDisconnect()
                break
            case WebSocket.CLOSING:
                terminal.print('Currently disconnecting...')
                break
            case WebSocket.CLOSED:
                // Currently disconnected, so connect
                terminal.print('Connecting... ')
                wsSocketConnect(receiveMsg, ws_ip, ws_port, ws_route)
                break
        }
        setTimeout(() => {
            if (wsSocketState() !== WebSocket.OPEN) {
                terminal.print('No connection')
            }
        }, 5000)
    })

    // Send the 'STATUS' command when status button clicked
    statusBtn.addEventListener('click', () => {
        sendMessage('STATUS', statusBtn)
    })

    startStateChecking()
})
