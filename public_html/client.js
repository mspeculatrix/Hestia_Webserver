/**
 * JavaScript for WebSockets client
 *
 * NB: There are lots of place where we use: wsState = wsSocketState()
 * This is because it's a good idea for the code to always be working with the
 * most up to date value. We check and update the state regularly with
 * startStateChecking(), but that's mainly to update things even when we're
 * doing nothing (eg, in case the connection drops).
 */

let ws_ip = '10.0.160.10'
const ws_port = '8080'
const ws_route = ''
const checkStateInterval = 1000
const buttonStatePause = 1000
let connectBtn

const directions = [
        'DIR_STOP',
        'FWD_STR',
        'FWD_TURN_L',
        'FWD_TURN_R',
        'REV_STR',
        'REV_TURN_L',
        'REV_TURN_R',
        'ROTATE_L',
        'ROTATE_R'
    ]

const speeds = [ 'SP_CRAWL', 'SP_SLOW', 'SP_NORMAL', 'SP_FAST', 'SP_MAX' ]

let speedCalL
let speedCalR
let baseSpeed
let adc0Trig
// let adc1Trig
// let adc2Trig

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
            switch (recvMsg['type']) {
                case 'CFG_STAT':
                    speedCalL.value = recvMsg['sp_cal_l']
                    speedCalR.value = recvMsg['sp_cal_r']
                    baseSpeed.value = recvMsg['base_speed']
                    adc0Trig.value = recvMsg['adc0_trigger']
                    // adc1Trig.value = recvMsg['adc1_trigger']
                    // adc2Trig.value = recvMsg['adc2_trigger']
                    break;
                default:
                    let pstr = []
                    for (const key in recvMsg) {
                        if(recvMsg[key]) {    // don't bother with blank entries
                            pstr.push(`${key}: ${recvMsg[key]}`)
                        }
                    }
                    terminal.print('-- ' + pstr.join(' | '))
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
        terminal.print(msg)
        let sent = wsSocketSend(msg)
        if (!sent) {
            terminal.print('Send failed!')
        }
    } else {
        terminal.print('No connection!')
    }
}

window.addEventListener('DOMContentLoaded', () => {
    connectBtn = document.getElementById('wsConnect')
    const ipInput = document.getElementById('cfg_ip')
    const statusBtn = document.getElementById('statusBtn')
    const messageText = document.getElementById('wsSendMsg')
    consoleEl = document.getElementById('console')
    speedCalL = document.getElementById('cfg_sp_cal_l')
    speedCalR = document.getElementById('cfg_sp_cal_r')
    baseSpeed = document.getElementById('cfg_base_sp')
    adc0Trig = document.getElementById('adc0_trigger')
    // adc1Trig = document.getElementById('adc1_trigger')
    // adc2Trig = document.getElementById('adc2_trigger')
    const cfgBtn = document.getElementById('cfgBtn')

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

    ipInput.value = ws_ip

    // Assign to outer variable
    terminal = new Console(consoleEl)

    // -------------------------------------------------------------------------
    // ---   DIRECTION & SPEED BUTTONS
    // -------------------------------------------------------------------------

    // Movement buttons
    setupButtonGroup(directions)

    // Speed buttons
    setupButtonGroup(speeds)

    // -------------------------------------------------------------------------
    // ---   CONTROL BUTTONS
    // -------------------------------------------------------------------------

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

    // Send the 'STATUS' command
    statusBtn.addEventListener('click', () => {
        clickBtn(statusBtn, '...')
        sendMessage('STATUS')
    })

    cfgBtn.addEventListener('click', () => {
        clickBtn(cfgBtn, '...')
        sendMessage('CONFIG')
    })

    // -------------------------------------------------------------------------
    // ---   INPUT FIELDS
    // -------------------------------------------------------------------------

    ipInput.addEventListener('keyup', (event) => {
        event.preventDefault();
        if (event.key === 'Enter') {
            ws_ip = ipInput.value
            ipInput.blur()
        }
    })

    // Send contents of message input field when <return> pressed
    messageText.addEventListener('keyup', (event) => {
        event.preventDefault();
        if (event.key === 'Enter') {
            sendMessage(messageText.value.toUpperCase())
            messageText.blur()
        }
    })

    speedCalL.addEventListener('keyup', (event) => {
        event.preventDefault();
        if (event.key === 'Enter') {
            let msg = 'CFG:SP_CAL_L:' + speedCalL.value
            sendMessage(msg)
            speedCalL.blur()
        }
    })

    speedCalR.addEventListener('keyup', (event) => {
        event.preventDefault();
        if (event.key === 'Enter') {
            let msg = 'CFG:SP_CAL_R:' + speedCalR.value
            sendMessage(msg)
            speedCalR.blur()
        }
    })

    baseSpeed.addEventListener('keyup', (event) => {
        event.preventDefault();
        if (event.key === 'Enter') {
            let msg = 'CFG:BASE_SPEED:' + baseSpeed.value
            sendMessage(msg)
            baseSpeed.blur()
        }
    })

    adc0Trig.addEventListener('keyup', (event) => {
        event.preventDefault();
        if (event.key === 'Enter') {
            let msg = 'CFG:ADC0_TRIG:' + adc0Trig.value
            sendMessage(msg)
            adc0Trig.blur()
        }
    })

    startStateChecking()
})
