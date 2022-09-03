<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style><?php include 'styles/base.css'; ?></style>
<style><?php include 'styles/declaration.css'; ?></style>
<style><?php include 'styles/booking.css'; ?></style>
<style><?php include 'styles/application-information.css'; ?></style>
<script src="./node_modules/jsencrypt/bin/jsencrypt.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=6Lf834whAAAAAGqCbtx_OUpjJRwOH0JhBG7AT7z9"></script>

<body>

<form id="regForm" action="javascript:void(0);">
    <input id="recaptcha" type="hidden" name="g-recaptcha-response" value="">
    <h2>Appointment Booking for Hong Kong Smart Identity Card</h2>
    <!-- One "tab" for each step in the form: -->
    <div class="tab">Declaration:
        <br/>
        <div class="declare">
            <?php
            include('./content/declaration.html');
            ?>
        </div>
        <br/>
        <div>
            <input class="agree-box" type="checkbox" id="agree-box" checked="true"><label class="agree-label-text"
                                                                                          for="agree-box">I
                have read and agree to the above Privacy Policy, Statement of Purpose, Copyright Notice and
                Disclaimer.</label>
        </div>
    </div>

    <div class="tab">Part A: The Applicant's Information<br/>
        <div class="grid-container">
            <h5>HKID</h5>
            <div class="grid-item">
                <input id='hkid' type="password" class='field'
                       placeholder="Hong Kong ID No." name="hkid"
                       maxlength="7">
                <p class="digit-field">(</p>
                <input id="hkid-last" type="password" class='digit-field' placeholder="" name="hkid-last"
                       maxlength="1">
                <p class="digit-field">)</p>
            </div>
            <br/>
            <h5>Date of birth</h5>
            <div>
                <input id='date' type="number" class='field' placeholder="Date (1-31)" min='1' max="31">
                <input id='year' type="number" class='field' placeholder="Year (1900-2022)"
                       name="hkid-last" min="1900"
                       max="2022">
            </div>
        </div>
    </div>
    <div class="tab">Contact Info:
        <input id='email' placeholder="E-mail..." name="email"/>
        <input id='phone' placeholder="Phone..." name="phone"/>
    </div>
    <div class="tab">Booking Info:
        <div class="booking">
            <?php
            include('./content/booking.html');
            ?>
        </div>
    </div>
    <br/>
    <div style="overflow:auto;">
        <div style="float:right;">
            <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
            <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
        </div>
    </div>
    <!-- Circles which indicates the steps of the form: -->
    <div style="text-align:center;margin-top:40px;">
        <span class="step"></span>
        <span class="step"></span>
        <span class="step"></span>
        <span class="step"></span>
    </div>
</form>


<script>
    let currentTab = 0; // Current tab is set to be the first tab (0)
    let hkid;
    let hkidLast;
    let date;
    let year;
    let email;
    let phone;
    let selectedDate;
    let selectedTime;

    showTab(currentTab); // Display the current tab

    function showTab(n) {
        // This function will display the specified tab of the form...
        const x = document.getElementsByClassName("tab");
        x[n].style.display = "block";
        //... and fix the Previous/Next buttons:
        if (n === 0) {
            document.getElementById("prevBtn").style.display = "none";
        } else {
            document.getElementById("prevBtn").style.display = "inline";
        }
        if (n === (x.length - 1)) {
            document.getElementById("nextBtn").innerHTML = "Submit";
        } else {
            document.getElementById("nextBtn").innerHTML = "Next";
        }
        //... and run a function that will display the correct step indicator:
        fixStepIndicator(n)
    }

    async function nextPrev(n) {
        const x = document.getElementsByClassName("tab");
        // Exit the function if any field in the current tab is invalid:
        const result = await validateForm()
        if (n === 1 && !result) return false;
        // Hide the current tab:
        x[currentTab].style.display = "none";
        // Increase or decrease the current tab by 1:
        currentTab = currentTab + n;
        // if you have reached the end of the form...
        if (currentTab >= x.length) {
            // ... the form gets submitted:
            await submitForm()

            return false;
        }
        // Otherwise, display the correct tab:
        showTab(currentTab);
    }

    async function validateForm() {
        // This function deals with validation of the form fields
        let x, y, i, valid = true;
        x = document.getElementsByClassName("tab");
        y = x[currentTab].getElementsByTagName("input");

        if (currentTab === 0) { // declaration
            valid = document.getElementById('agree-box').checked
            if (!valid) alert('Please tick the check box of the Declaration at the bottom of this page to proceed with the application')
        } else if (currentTab === 1) { // applicant information
            hkid = document.getElementById('hkid').value
            hkidLast = document.getElementById('hkid-last').value
            date = document.getElementById('date').value
            year = document.getElementById('year').value

            if (hkid && hkidLast && date && year) {
                if (date <= 0 || date > 31 || year < 1900 || year > 2022) {
                    valid = false
                    alert('Invalid Date/Year')
                } else {
                    const regexHKID = new RegExp('^[ABCNORUWXYZ]{1}[0-9]{6}$');
                    const regexHKIDLast = new RegExp('^[0-9]{1}$')

                    if (!regexHKID.test(hkid) && !regexHKIDLast.test(hkidLast)) {
                        alert('Invalid HKID Format (The first character must be capital)')
                        valid = false
                    } else {

                        // Encrypt with the public key...
                        var encrypt = new JSEncrypt();
                        encrypt.setPublicKey(`-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvxP23F+kpX4Feuu+46s3
nbIACMpujhSOaRcUozm69V0rqSeA0ibIRrZhNHlmm3dbOI1Kcrz6wqymYtswpzll
2hcUIrAq4IhP4DRlrFBBdQoIQH7nzB2XbN7WcKC0si8U2PxS9YdhwxTrMU6ukMP/
nigYdHAU8Xcwqoo5y6No0IRgwaTpZ3bcoiJx1/kTp5r939l6DDkW16WJRXFwbaBw
ZZGo26MFXXVeqyBI/DsfqOpKzRubb2O2mP+sJMsVQYFj07xxiebq0iDC9DPFlCPt
u95f4xlTZdTihurKg3hQmUaYbZ7ERx2uDglDYHDLwaVewwuFxrTk/K5pqYbk1HpO
VwIDAQAB
-----END PUBLIC KEY-----
`);

                        // send request out to validate
                        const res = await fetch('http://localhost:8081/api/user/validate', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({data: encrypt.encrypt(JSON.stringify({hkid: hkid + hkidLast, date, year}))})
                        });
                        const content = await res.json();
                        // either true or false
                        valid = !!content;
                        if (!valid) alert('Validate failed')
                    }
                }
            } else {
                alert('Please Input HKID Number and Date of Birth')
                valid = false
            }
        } else if (currentTab === 2) {
            email = document.getElementById('email').value
            phone = document.getElementById('phone').value

            const regexEmail = new RegExp('^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$');
            const regexPhone = new RegExp('^[0-9]{8}$')

            if (!regexEmail.test(email) && !regexPhone.test(phone)) {
                alert('Invalid Email or Phone Format')
                valid = false
            }
        } else {
            // A loop that checks every input field in the current tab:
            for (i = 0; i < y.length; i++) {
                // If a field is empty...
                if (y[i].value === "") {
                    // add an "invalid" class to the field:
                    y[i].className += " invalid";
                    // and set the current valid status to false
                    valid = false;
                }
            }
        }


        // If the valid status is true, mark the step as finished and valid:
        if (valid) {
            document.getElementsByClassName("step")[currentTab].className += " finish";
        }
        return valid; // return the valid status
    }

    function fixStepIndicator(n) {
        // This function removes the "active" class of all steps...
        let i, x = document.getElementsByClassName("step");
        for (i = 0; i < x.length; i++) {
            x[i].className = x[i].className.replace(" active", "");
        }
        //... and adds the "active" class on the current step:
        x[n].className += " active";
    }

    const selectDate = date => selectedDate = date

    const selectTime = async time => selectedTime = time

    const encryptStringWithRsaPublicKey = (toEncrypt, publicKey) => {
        const buffer = Buffer.from(toEncrypt, 'utf8');
        const encrypted = crypto.publicEncrypt(publicKey, buffer);
        return encrypted.toString("base64");
    };

    const submitForm = async () => {
        const location = document.getElementById('location').value;


        if (currentTab === 4 && // make sure on the last page
            hkid && hkidLast && date && year && email && phone
            && selectedDate && selectedTime && location) {
            // submit to backend server
            // get recaptcha token
            const token = document.getElementById('recaptcha').value
            const res = await fetch('http://localhost:8081/api/user/submit', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    hkid: hkid + hkidLast,
                    date, year, email, phone,
                    selectedDate, selectedTime, location, token
                })
            });
            const content = await res.json();
            if (content?.result) {
                alert('Success! See ya on that date')
            } else alert('Failed! Wrong data or this HKID may be already registered.')
            window.location.reload();
        } else alert('Missing Parameter(s)')
    }

</script>

<script>
    let app = {
        settings: {
            container: document.getElementsByClassName("calendar"),
            calendar: document.getElementsByClassName("front"),
            days: document.querySelectorAll(".weeks span"),
            timeslot: document.querySelectorAll(".back .timeSlot button"),
            form: document.getElementsByClassName("back"),
            input: document.querySelectorAll(".back input"),
        }, init: function () {
            instance = this;
            settings = this.settings;
            this.bindUIActions();
        }, swap: function (currentSide, desiredSide) {
            settings.container[0].classList.toggle("flip");
            // currentSide.remove();
            currentSide.style.display = "none";
            desiredSide.style.display = "";
        }, bindUIActions: function () {
            settings.days.forEach(i => i.addEventListener('click', (e) => {
                selectDate(i.innerHTML)
                instance.swap(settings.calendar[0], settings.form[0]);
            }));
            settings.timeslot.forEach(i => i.addEventListener('click', async e => {

                await selectTime(i.value)
                document.getElementById('selected-date').innerHTML = i.value
                // await submitForm()
            }))
        }
    }
    app.init();
</script>

<script>
    grecaptcha.ready(function () {
        grecaptcha.execute('6Lf834whAAAAAGqCbtx_OUpjJRwOH0JhBG7AT7z9', {action: 'submit'}).then(function (token) {
            // pass the token to the backend script for verification
            document.getElementById('recaptcha').value = token
        });
    });
</script>
</body>
</html>