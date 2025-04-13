<?php 
    session_start();
    require "../html/includes/dbCon.php";
    $error_message="";
    $successful_message="";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once "../html/includes/formHandler.php";
        
        if (isset($_POST['register_joiner_submit'])) {
            $result = registerUser ($pdo, $_POST);
            $error_message = $result['message'];
            $successful_message = $result['success_message'];
        } elseif (isset($_POST['register_organizer_submit'])) {    

        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JOYn</title>

    <link rel="stylesheet" type="text/css" href="../css/nav_styles.css"> 
    <link rel="stylesheet" type="text/css" href="../css/landing_page.css">
</head>
<body>
    <!-- NAVBAR START -->
    <nav id="nav">
        <div class="nav_left">
            <ul class = "navbar">
                <li><input type="button" class="logo"></li>
                <li><a href="" onclick="openModal('modal_selection', event)">Home</a></li>
                <li><a href="" onclick="openModal('modal_selection', event)">Activity</a></li>
                <li><a href="" onclick="openModal('modal_selection', event)">Forum</a></li>
                <li><a href="" onclick="openModal('modal_selection', event)">Marketplace</a></li>
                <li><a href="" onclick="openModal('modal_selection', event)">Notification</a></li>
            </ul>
        </div>
        <div class="nav_right">   
            <input type="text" placeholder = "Search" class = "searchBar">
            <input type="button" class = "signup_button" value="Sign Up" onclick="openModal('modal_selection', event)">
        </div>          
    </nav>  
    <!-- NAVBAR END -->
    
    <!-- CONTENT START-->
    <div class="container">
        <div class="hero_section">
            <div class="hero_content">
                <h1>JOYn</h1>
                <h2>Find your buddy and explore recreational activities with ease</h2>
            </div>

            <div class="hero_button">
                <input type="button" class = "learnmore_button" value = "Learn More">
            </div>
        </div>

        <div class="about_section">
            <div class="about_text">
                <h1>About our Website</h1>
                <p>
                    JOYn is an innovative web portal designed to help people discover and join recreational 
                    activities in their area. Whether you're into hiking or adventure trips. JOYn makes it 
                    easy to connect with others who share your interests. Our platform features an intuitive 
                    3D mapping system, real-time SMS notifications, and a seamless event discovery experience.
                    Start exploring today and make meaningful connections!
                </p>
            </div>
            <div class="about_image">
                <img src="../imgs/langpage_about.jpg" alt="" height="600px" class = "about_image_image">
            </div>
        </div>

        <div class="widget_section">
            <div class="widget">
                <img src="../imgs/icon_location.png" alt="" height="30px">
                <h3>3D Mapping</h3>
                <p>Look at your target destination and visualize your hiking experience.</p>
            </div>
            <div class="widget">
                <img src="../imgs/icon_notification.png" alt="" height="30px">
                <h3>Notification</h3>
                <p>Communicate with your travel buddies real time.</p>
            </div>
            <div class="widget">
                <img src="../imgs/icon_marketplace.png" alt="" height="30px">
                <h3>Marketplace</h3>
                <p>Sell or exchange your gears on our marketplace.</p>
            </div>
            <div class="widget">
                <img src="../imgs/icon_joining.png" alt="" height="30px">
                <h3>Easy Event Joining</h3>
                <p>Join various hiking and adventure events seamlessly.</p>
            </div>
        </div>

        <div class="start_section">
            <div class="start_header">
                <h1>How to start</h1>
                <img src="../imgs/icon_arrow.png" alt="" height="60px" id = "start_icon">
            </div>
            
            <div class="start_widget">
                <div class="start_text">
                    <img src="../imgs/icon_signup.png" alt="" height="80px" id = "start_image">
                    <h3>SIGN UP</h3>
                    <p>Create a joiner account through the sign up button.</p>
                </div>
                <div class="start_text">
                    <img src="../icon_search.png" alt="" height="80px" id = "start_image">
                    <h3>DISCOVER</h3>
                    <p>Browse activities filtered to your liking.</p>
                </div>
                <div class="start_text">
                    <img src="../icon_join.png" alt="" height="80px" id = "start_image">
                    <h3>JOIN & CONNECT</h3>
                    <p>Participate in activites made by the organizers.</p>
                </div>
                <div class="start_text">
                    <img src="../imgs/icon_notification.png" alt="" height="80px" id = "start_image">
                    <H3>GET NOTIFIED</H3>
                    <p>Receive alerts when the activity is near.</p>
                </div>        
            </div>
        </div>

        <div class="slideshow_section">
            <div class="slideshow_header">
                <h1>Trails</h1>
                <img src="../imgs/icon_arrow.png" alt="" height="60px" id = "start_icon">
            </div>   

            <div class="slideshow">
                <div class="slides fade">
                    <h2>Mount Daraitan</h2>
                    <img src="../imgs/slideshow_img/slide_one.jpg" alt="Image 1">
                </div>
                <div class="slides fade">
                    <h2>Mount Pinatubo</h2>
                    <img src="../imgs/slideshow_img/slide_two.jpg">
                </div>
                <div class="slides fade">
                    <h2>Mount Ulap</h2>
                    <img src="../imgs/slideshow_img/slide_three.jpg" alt="Image 3">
                </div>
                <a class="prev" onclick="changeSlide(-1)">&#10094;</a>
                <a class="next" onclick="changeSlide(1)">&#10095;</a>
            </div>
            
        </div>

        <div class="start_section">
            <div class="start_header_organizer">
                <h1 id = "start_header">Join as Organizer</h1>
                <img src="../imgs/icon_arrow.png" alt="" height="60px" id = "start_icon">
            </div>  
            <div class="start_widget">
                <div class="start_text">
                    <img src="../imgs/icon_signup.png" alt="" height="80px" id = "start_image">
                    <h3>SIGN UP</h3>
                    <p>Create an account with the necessary documents.</p>
                </div>
                <div class="start_text">
                    <img src="../imgs/icon_activity.png" alt="" height="80px" id = "start_image">
                    <h3>ORGANIZE AN EVENT</h3>
                    <p>Facilitate and event by listing an activity.</p>
                </div>
                <div class="start_text">
                    <img src="../imgs/icon_notification.png" alt="" height="80px" id = "start_image">
                    <h3>NOTIFY</h3>
                    <p>Send messages to users when activity is near.</p>
                </div>
                <div class="start_text">
                    <img src="../imgs/icon_review.png" alt="" height="80px" id = "start_image">
                    <H3>GATHER FEEDBACK</H3>
                    <p>Collect reviews and suggestions to improve future adventures.</p>
                </div>        
            </div>

        </div>

        <footer id="footer">
            <div class="footer_logo">
                <img src="../imgs/logo.png" alt="">
            </div>
            <div class="footer_section_one">
                <h1>JOYn</h1>
                <p>Find your buddy and explore recreational activities with ease.</p>
                <p>© 2025-2026. All Rights Reserved</p>
            </div>
            <div class="footer_section_two">
                <h2>Support</h2>
                <p><a href="#">Contact Us</a></p>
                <p><a href="#">Affliate</a></p>
                <p><a href="#">API</a></p>
            </div>
            <div class="footer_section_three">
                <h2>Legal</h2>
                <p><a href="#">Terms & Conditions</a></p>
                <p><a href="#">Privacy Policy</a></p>
            </div>
            <div class="footer_section_four">
                <h2>FAQ</h2>
                <p><a href="#">About Us</a></p>
                <p><a href="#">Blogs</a></p>
            </div>
        </footer>
        <!-- CONTENT END-->

        <!-- MODAL DIALOGUES -->
        <div id="modal_selection" class ="modal">      
            <div class="modal_content">
                <div class="icon_arrow">
                    <button class = "button_arrow" onclick="closeModal('modal_selection')"></button>
                </div>       
                <h3 style="font-size:2em;">Sign up as</h3>
                <button class = "modal_selection_button" onclick="openModal('organizer_modal', event)">Organizer</button>
                <button class = "modal_selection_button" onclick="openModal('joiner_modal', event)">Joiner</button>
                <h4 style="font-size: 0.8em;">Already has an account? <button onclick="window.location.href=window.location.href='login.php';" 
                style="background: none; border: none; color: blue; cursor: pointer; text-decoration: underline; padding: 3px;">Click here</button> to login.</h4>
                <p>
                    Choose "Joiner" if you are enthusiast wishing to participate in activities. However, if you own
                    a travel business and seeking to use this platform to facilitate activites, choose "Organizer"
                    and submit the necessary documents.
                </p>
            </div>
        </div>
        

        <div id="joiner_modal" class="modal">
            <div class="modal_content">
                    <div class="icon_arrow">
                        <button class = "button_arrow" onclick="closeModal('joiner_modal')"></button>
                    </div>       
                    <h1 style="font-size:2em;">Register</h1>
                    <h4 style="color: lightgray;">*Fill up all the blanks*</h4>

                    
                    <form method="POST" action="" autocomplete="off" onsubmit="return validatePassword();">
                        <div id="input_group">
                            <input type="text" name="firstname" placeholder="Firstname" required class="joiner_registration">
                            <input type="text" name="lastname" placeholder="Lastname" required class="joiner_registration">
                            <input type="password" name="password" placeholder="Password" id="password" required class="joiner_registration" minlength="9">
                            <input type="password" name="cpassword" placeholder="Confirm Password" id="cpassword" required class="joiner_registration" minlength="9">
                            <input type="email" name="email" placeholder="Email Address" required class="joiner_registration">
                            <div class="joiner_registration" id="radio_group">
                                <h5 style="text-align: left; padding-left: 47px;">Gender:</h5>
                                <label>
                                    <input type="radio" name="gender" value="Male" required style="width: 30px"> Male
                                </label>
                                <label>
                                    <input type="radio" name="gender" value="Female" required style="width: 30px"> Female
                                </label>
                                <label>
                                    <input type="radio" name="gender" value="Other" required style="width: 30px"> Other
                                </label>
                            </div>
                            <input type="text" name="address" placeholder="Brgy, Locality, Province" required class="joiner_registration"
                                pattern="^[a-zA-Z\s]+,\s[a-zA-Z\s]+,\s[a-zA-Z\s]+$">
                            <input type="tel" name="contactnumber" placeholder="Contact Number" required class="joiner_registration"  maxlength="11" pattern="\d{11}"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                            <input type="text" name="emergencyConName" placeholder="Emergency Contact Name" required class="joiner_registration">
                            <input type="text" name="emergencyConNumber" placeholder="Emergency Contact Number" required class="joiner_registration" 
                                maxlength="11" pattern="\d{11}"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57"> 
                        </div>   
                        <div style="width: 100%; position: relative; left: 100px; margin-top: 10px; margin-bottom: 10px;">
                            <button class="submit_button" type="submit" name="register_joiner_submit">Submit</button>
                        </div>                       
                    </form>
            </div>
        </div>
        
        <div id="organizer_modal" class="modal">
            <div class="modal_content">
                    <div class="icon_arrow">
                        <button class = "button_arrow" onclick="closeModal('organizer_modal')"></button>
                    </div>       
                    <h1 style="font-size:2em;">Register</h1>
                    <h4 style="color: lightgray;">*Fill up all the blanks*</h4>

                <form method="POST" action="" autocomplete="off" enctype="multipart/form-data">
                    <div id="input_group">
                        
                        <input type="text" name="orgname" placeholder="Organization Name" required>
                        <input type="password" name="orgpass" placeholder="Password" required minlength="9">
                        <input type="email" name="orgemail" placeholder="Email Address" required>
                        <input type="text" name="CEO" placeholder="CEO" required>
                        <input type="text" name="orgadd" placeholder="Brgy-Locality-Province" required pattern="^[a-zA-Z\s]+,\s[a-zA-Z\s]+,\s[a-zA-Z\s]+$">
                        <input type="tel" name="orgnumber" placeholder="Contact Number" required maxlength="11" pattern="\d{11}"
                        onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        <input type="file" name="orgpdf[]" accept=".pdf" multiple required class="uploadBtn">
                        <div style="width: 100%; position: relative; left: 100px; margin-top: 10px; margin-bottom: 10px;">
                            <button class="submit_button" type="submit" name="register_organizer_submit">Submit</button>
                        </div>         
                        <p>Upload the required pdf files consisting of your Department of Tourism(DOT) permit and Bureau of Internal(BIR) Revenue Certificate(COR).</p>
                    </div>                    
                </form>
            </div>
        </div>
        <!-- END OF MODAL DIALOGUES -->

        <div id="toast" style="display: none; position: fixed; background-color:red; color: white; padding: 10px; border-radius: 5px; bottom: 20px; right: 20px;">
            <span id="toast-message"></span>
        </div>
        <div id="good_toast" style="display: none; position: fixed; background-color:green; color: white; padding: 10px; border-radius: 5px; bottom: 20px; right: 20px;">
            <span id="good-toast-message"></span>
        </div>
        <script>
            function validatePassword() {
                let pw = document.getElementById("password").value;
                let cpw = document.getElementById("cpassword").value;

                if (pw !== cpw) {
                    alert("Passwords do not match!");
                    return false;
                }
                return true;
            }

            //MODAL INTERACTION
            function openModal(modalId, event) {
                event.preventDefault();
                const modal = document.getElementById(modalId);
                modal.classList.add('show');
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                modal.classList.remove('show');
            }
            function openLoginModal() {
                closeModal('modal_selection'); 
                openModal('login_modal');
            }

            window.onclick = function(event) {
                if (event.target.classList.contains("modal")) {
                    event.target.classList.remove('show');
                }
            };

            function validateForm() {
                var password = document.getElementById("orgpass").value;
                if (password.length < 9) {
                    alert("Password must be 8 characters above.");
                    return false; 
                }
                return true;
            }
            //MODAL END

            //SLIDESHOW LANDING PAGE INTERACTION
            let slideIndex = 0;
            showSlides();

            function showSlides() {
                const slides = document.getElementsByClassName("slides");
                for (let i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none"; // Hide all slides
                }
                slideIndex++;
                if (slideIndex > slides.length) { slideIndex = 1 } // Reset to first slide
                slides[slideIndex - 1].style.display = "block"; // Show the current slide
                setTimeout(showSlides, 3000); // Change slide every 3 seconds
            }

            function changeSlide(n) {
                slideIndex += n;
                const slides = document.getElementsByClassName("slides");
                if (slideIndex > slides.length) { slideIndex = 1 }
                if (slideIndex < 1) { slideIndex = slides.length }
                for (let i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none"; // Hide all slides
                }
                slides[slideIndex - 1].style.display = "block"; // Show the current slide
            }
            //SLIDESHOW END

            //NAVBAR INTERACTION
            const navbar = document.getElementById('nav');
            function handleScroll() {
                if (window.scrollY > 0) {
                    navbar.classList.add('scrolled'); // Add class when scrolled
                } else {
                    navbar.classList.remove('scrolled'); // Remove class when at the top
                }
            }
            window.addEventListener('scroll', handleScroll);
            //NAVBAR END

            //toast 
            const errorMessage = "<?php echo addslashes($error_message); ?>";
            const successMessage = "<?php echo addslashes($successful_message); ?>";

            if (errorMessage) {
                const errorToast = document.getElementById('toast');
                const errorToastMessage = document.getElementById('toast-message');
                errorToastMessage.innerText = errorMessage;
                errorToast.style.display = 'block';
                setTimeout(() => {
                    errorToast.style.display = 'none';
                }, 3000);
            }

            if (successMessage) {
                const successToast = document.getElementById('good_toast');
                const successToastMessage = document.getElementById('good-toast-message');
                successToastMessage.innerText = successMessage;
                successToast.style.display = 'block';
                setTimeout(() => {
                    successToast.style.display = 'none';
                }, 3000);
            }
        </script>
    </div>

</body>
</html>