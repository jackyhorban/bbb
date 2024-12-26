<?php 
    $checkout_processor = $_SESSION['checkout_processor'];
    $current_form_status = 0;
    if(is_user_logged_in() && current_user_can('Customer') && !current_user_can('administrator')){
        global $wpdb;
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        $clients_table = $wpdb->prefix . "boir_clients";
        $clients_fillings_table = $wpdb->prefix . "boir_clients_fillings";
        $fillings_inital_data_table = $wpdb->prefix . "boir_fillings_inital_data";
        $fillings_jurisdiction_data_table = $wpdb->prefix . "boir_fillings_jurisdiction_data";
        $fillings_company_applicants_table = $wpdb->prefix . "boir_fillings_company_applicants";
        $fillings_beneficial_owners_table = $wpdb->prefix . "boir_fillings_beneficial_owners";
        $fillings_payments_table = $wpdb->prefix . "boir_fillings_payments";

        $client_info = $wpdb->get_row("SELECT * FROM $clients_table WHERE user_id = $user_id");
        if($client_info){
            $client_id = $client_info->id;
            $client_name = $client_info->client_name;
            $client_email = $client_info->client_email;
            $client_phone = $client_info->client_phone;
            $current_form_status = 1;
            
            if(isset($_GET['filling_id']) && $_GET['filling_id']!=''){
                $filling_id = $_GET['filling_id'];
                $filling_info = $wpdb->get_row("SELECT * FROM $clients_fillings_table WHERE id = $filling_id AND client_id = $client_id");
                if($filling_info){
                    $filling_status = $filling_info->filling_status;
                    if($filling_status>1){
                        echo "<center><b style='color:red;'>This filling has already been filled.</b></center>";
                        $hide_form = true;
                    }else{
                        $wpdb->query("UPDATE $clients_fillings_table SET filling_status = 0 WHERE client_id = $client_id AND filling_status = 1");
                        $wpdb->query("UPDATE $clients_fillings_table SET filling_status = 1 WHERE id = $filling_id");
                    }
                }else{
                    echo "<center><b style='color:red;'>This filling does not exist.</b></center>";
                    $hide_form = true;
                }
            }else{
                $filling_info = $wpdb->get_row("SELECT * FROM $clients_fillings_table WHERE client_id = $client_id AND filling_status = 1 ORDER BY id DESC LIMIT 1");
            }
            
            if($filling_info){
                $filling_id = $filling_info->id;
                $filling_status = $filling_info->filling_status;
                $filling_code = $filling_info->filling_code;
                $client_fincen_id = $filling_info->client_fincen_id;


                $existing_reporting_company = $filling_info->existing_reporting_company;
                $filling_authorization = $filling_info->filling_authorization;
                $initial_info = $wpdb->get_row("SELECT * FROM $fillings_inital_data_table WHERE filling_id = $filling_info->id");

                $initial_legal_name = $initial_info->initial_legal_name;
                $initial_alternate_name = $initial_info->initial_alternate_name;
                $initial_tax_type = $initial_info->initial_tax_type;    
                $initial_tax_number = $initial_info->initial_tax_number;

                $current_form_status = 2;
                $jurisdiction_info = $wpdb->get_row("SELECT * FROM $fillings_jurisdiction_data_table WHERE filling_id = $filling_info->id");
                if($jurisdiction_info){
                    $juri_formation_country = $jurisdiction_info->juri_formation_country;
                    $juri_formation_state = $jurisdiction_info->juri_formation_state;
                    $juri_tribal = $jurisdiction_info->juri_tribal;
                    $juri_other_tribe = $jurisdiction_info->juri_other_tribe;
                    $juri_address_line_1 = $jurisdiction_info->juri_address_line_1;
                    $juri_address_line_2 = $jurisdiction_info->juri_address_line_2;
                    $juri_city = $jurisdiction_info->juri_city;
                    $juri_state = $jurisdiction_info->juri_state;
                    $juri_zip = $jurisdiction_info->juri_zip;
                    $current_form_status = 3;
                    if($existing_reporting_company=='yes'){
                        $fillings_company_applicants_check = $wpdb->get_row("SELECT * FROM $fillings_company_applicants_table WHERE filling_id = $filling_info->id");
                        if($fillings_company_applicants_check){
                            $fillings_company_applicants_status = 'true';
                            $current_form_status = 4;
                        }else{
                            $fillings_company_applicants_status = 'false';
                        }
                    }elseif($existing_reporting_company=='no'){
                        $current_form_status = 4;
                    }else{
                        $current_form_status = 3;
                    }
                    if($current_form_status>3){
                        $fillings_beneficial_owners_check = $wpdb->get_row("SELECT * FROM $fillings_beneficial_owners_table WHERE filling_id = $filling_info->id");
                        if($fillings_beneficial_owners_check){
                            $fillings_beneficial_owners_status = 'true';
                            $current_form_status = 5;
                        }else{
                            $fillings_beneficial_owners_status = 'false';
                            $current_form_status = 4;
                        }
                    }
                    if($current_form_status>4){
                        if($filling_authorization=='1'){
                            $fillings_payments_check = $wpdb->get_row("SELECT * FROM $fillings_payments_table WHERE filling_id = $filling_info->id");
                            if($fillings_payments_check){
                                $fillings_payments_status = 'true';
                            }else{
                                $fillings_payments_status = 'false';
                            }
                            $current_form_status = 5;
                        }else{
                            $current_form_status = 5;
                        }
                    }
                }
            }
        }else{
            $current_form_status = 0;
        }

    }elseif(is_user_logged_in() && current_user_can('administrator')){
        $current_form_status = 0;
        echo "<center><b style='color:red;'>Dear Administrator, Please log out to Test this form correctly.</b><br><i>(This message is only visible for administrators)</i></center>";
    }else{
        $current_form_status = 0;
    }

    if($current_form_status==0){
        $tab = 1;
        $step = 1;
    }elseif($current_form_status==1){
        $tab = 1;
        $step = 1;
    }elseif($current_form_status==2){
        $tab = 2;
        $step = 1;
    }elseif($current_form_status==3){
        $tab = 2;
        $step = 2;
    }elseif($current_form_status==4){
        $tab = 2;
        $step = 3;
    }elseif($current_form_status==5){
        $tab = 2;
        $step = 4;
    }elseif($current_form_status==6){
        $tab = 3;
        $step = 5;
    }else{
        $tab = 1;
        $step = 1;
    }

    $existing_reporting_company = $existing_reporting_company ?? '';
    $fillings_company_applicants_status = $fillings_company_applicants_status ?? 'false';
    $fillings_beneficial_owners_status = $fillings_beneficial_owners_status ?? 'false';
    $filling_authorization = $filling_authorization ?? '0';
    $fillings_payments_status = $fillings_payments_status ?? 'false';

    
    $user_id = $user_id ?? '';
    $client_id = $client_id ?? '';
    $client_name = $client_name ?? '';
    $client_email = $client_email ?? '';
    $client_phone = $client_phone ?? '';
    $filling_id = $filling_id ?? '';
    $filling_code = $filling_code ?? '';
    $client_fincen_id = $client_fincen_id ?? '';
    $initial_legal_name = $initial_legal_name ?? '';
    $initial_alternate_name = $initial_alternate_name ?? '';
    $initial_tax_type = $initial_tax_type ?? '';    
    $initial_tax_number = $initial_tax_number ?? '';
    $juri_formation_country = $juri_formation_country ?? '';
    $juri_formation_state = $juri_formation_state ?? '';
    $juri_tribal = $juri_tribal ?? '';
    $juri_other_tribe = $juri_other_tribe ?? '';
    $juri_address_line_1 = $juri_address_line_1 ?? '';
    $juri_address_line_2 = $juri_address_line_2 ?? '';
    $juri_city = $juri_city ?? '';
    $juri_state = $juri_state ?? '';
    $juri_zip = $juri_zip ?? '';
    if(!isset($hide_form)){
?>

<div class="overlay"></div>
<div class="payment-error-pop ">
		<h5 class="fields-title"><i class="bi bi-info-circle-fill"></i>The payment will not approve until you contact your bank</h5>
	<div>The payment was declined. <br>Reason: <span class='pay-error-res'></span>
	</div>
	<div class='pop-btn'>
		<button id='pop-close' class="button-primary">Retry</button>				
	</div>

</div>
<div class="payee-popup" id="payeePopup" style="display: none;">
    <h5>Does your bank have debit block or positive pay set up</h5>
    <div class="popup-content">
        <p>If you utilize banking tools such as Filter, Debit Block, or Positive Pay, please ensure our Payee ID is whitelisted to prevent your transaction from being rejected.</p>
        <p>A rejected transaction may prevent your report from being filed, potentially leading to penalties.</p>
    </div>
    <div class="popup-content border-line">
        <div class="content-note">BOIR.ORG Payee ID</div>
        <div class="payee-id">1870843514</div>
    </div>
    <div class='pop-btn'>
		<div class="btn" id="countdownBtn">5</div>
		<button class="close-btn" id="closePopup">Continue</button>
	</div>
</div>

<div class="header-con">

            <div class="header-content <?=$tab==1?'active':''?> tab-1" id="BOIRFormHeader1">
                <div class="header-title">Start your BOIR Filing</div>
                <p class="header-description">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
            </div>


            <div class="header-content <?=$tab==2?'active':''?> tab-2" id="BOIRFormHeader2">
                <div class="steps-header">
                    <div class="step-header <?=$step==1?'':'hidden'?>" data-step="1">
                        <div class="header-title">File Your Beneficial Ownership Information Report (BOIR)</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                    <div class="step-header <?=$step==2?'':'hidden'?>" data-step="2">
                        <div class="header-title">Company Applicants Information</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                    <div class="step-header <?=$step==3?'':'hidden'?>" data-step="3">
                        <div class="header-title">Beneficial Owners Information</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                    <div class="step-header <?=$step==4?'':'hidden'?>" data-step="4">
                        <div class="header-title">Review & Submit</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                </div>
                <div class="steps-buttons hidden">
                    <div class="step-button <?=$step==1?'active':''?> <?=$step>1?'completed':''?> <?=$step>=1?'pressable':''?>" data-step="1">
                        <i class="bi bi-check"></i><span>Company Details</span>
                    </div>
                    <div class="step-button <?=$step==2?'active':''?> <?=$step>2?'completed':''?> <?=$step>=2?'pressable':''?>" data-step="2">
                        <i class="bi bi-check"></i><span>Company Applicants</span>
                    </div>
                    <div class="step-button <?=$step==3?'active':''?> <?=$step>3?'completed':''?> <?=$step>=3?'pressable':''?>" data-step="3">
                        <i class="bi bi-check"></i><span>Beneficial Owners</span>
                    </div>
                    <div class="step-button <?=$step>=4?'active':''?> <?=$step>4?'completed':''?> <?=$step>=4?'pressable':''?>" data-step="4">
                        <i class="bi bi-check"></i><span>Review & Submit</span>
                    </div>
                </div>
            </div>

            <div class="header-content <?=$tab==3?'active':''?> tab-3 tab-con">
                <div class="header-title">Complete Your Beneficial Ownership Information Report (BOIR)</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>

            </div>




        </div>

<main class="boir-manager-container" id="boirManagerForm">
    <input type="hidden" name="client_id" id="client_id" value="<?=$client_id?>">
    <input type="hidden" name="filling_id" id="filling_id" value="<?=$filling_id?>">
    <input type="hidden" name="filling_code" id="filling_code" value="<?=$filling_code?>">
    <div class="tabs-header">
        <div class="tabs-buttons hidden">
            <div class="tab-button <?=$tab==1?'active':''?> <?=$tab>=1?'pressable':''?>" data-tab="1">
                <div class="tab-circle <?=$tab==1?'active':''?> <?=$tab>1?'completed':''?>">
                    <span><i class="bi bi-check"></i></span>
                </div>
                <div class="tab-content">
                    <span>01</span>
                    <b>Initial Information</b>
                </div>
            </div>
            <div class="tab-button <?=$tab==2?'active':''?> <?=$tab>=2?'pressable':''?>" data-tab="2">
                <div class="tab-circle <?=$tab==2?'active':''?> <?=$tab>2?'completed':''?>">
                    <span><i class="bi bi-check"></i></span>
                </div>
                <div class="tab-content">
                    <span>02</span>
                    <b>Filling Details</b>
                </div>
            </div>
            <div class="tab-button <?=$tab==3?'active':''?> <?=$tab>=3?'pressable':''?>" data-tab="3">
                <div class="tab-circle <?=$tab==3?'active':''?> <?=$tab>3?'completed':''?>">
                    <span><i class="bi bi-check"></i></span>
                </div>
                <div class="tab-content">
                    <span>03</span>
                    <b>Payment Information</b>
                </div>
            </div>
        </div>
        <div class="tabs-progress tab-<?=$tab?>-active hidden">
            <div class="bar"></div>
        </div>
        <div class="tabs-header-content">
            <div class="header-content <?=$tab==1?'active':''?> tab-1" id="BOIRFormHeader1">
                <div class="special-box">
                    <div class="box-title">Request to receive FinCEN Identifier (FinCEN ID)</div>
                    <p class="box-content">By checking this box, You are Requesting to receive a FinCEN Idontitior FinCEN ID. You ma use this ID to mako future filings with FinCEN.</p>
                    <label class="box-checkbox" for="request_fincen_id">
                        <input class="checkbox" type="checkbox" name="request_fincen_id" id="request_fincen_id" value="1" checked <?=$client_fincen_id==1?'checked':''?>>
                        <label for="request_fincen_id">I would like to receive my FinCEN Identifier</label>
                    </label>
                </div>
            </div>
            <!-- <div class="header-content <?=$tab==2?'active':''?> tab-2" id="BOIRFormHeader2">
                <div class="steps-header">
                    <div class="step-header <?=$step==1?'':'hidden'?>" data-step="1">
                        <div class="header-title">Company Details</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                    <div class="step-header <?=$step==2?'':'hidden'?>" data-step="2">
                        <div class="header-title">Company Applicants Information</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                    <div class="step-header <?=$step==3?'':'hidden'?>" data-step="3">
                        <div class="header-title">Beneficial Owners Information</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                    <div class="step-header <?=$step==4?'':'hidden'?>" data-step="4">
                        <div class="header-title">Review & Submit</div>
                        <p class="header-description" style="padding-bottom: 0; margin-bottom: 0;">Filing is easy and secure. Just fill out the form below and we'll get started on your filing.</p>
                    </div>
                </div>
                <div class="steps-buttons hidden">
                    <div class="step-button <?=$step==1?'active':''?> <?=$step>1?'completed':''?> <?=$step>=1?'pressable':''?>" data-step="1">
                        <i class="bi bi-check"></i><span>Company Details</span>
                    </div>
                    <div class="step-button <?=$step==2?'active':''?> <?=$step>2?'completed':''?> <?=$step>=2?'pressable':''?>" data-step="2">
                        <i class="bi bi-check"></i><span>Company Applicants</span>
                    </div>
                    <div class="step-button <?=$step==3?'active':''?> <?=$step>3?'completed':''?> <?=$step>=3?'pressable':''?>" data-step="3">
                        <i class="bi bi-check"></i><span>Beneficial Owners</span>
                    </div>
                    <div class="step-button <?=$step>=4?'active':''?> <?=$step>4?'completed':''?> <?=$step>=4?'pressable':''?>" data-step="4">
                        <i class="bi bi-check"></i><span>Review & Submit</span>
                    </div>
                </div>
            </div> -->

            <div class="header-content <?=$tab==3?'active':''?> tab-3 tab-des">
                <div class="header-description">
                    <p>The Beneficial Ownership Information Report (BOIR) is a legal requirement under the 
                    Corporate Transparency Act (CTA). Non-compliance can result in severe penalties, including 
                    fines and legal action. To ensure your business is fully compliant, please complete your BOIR 
                    today by filing through our online portal. The current filing fee is $349. There is an additional charge 
                    for processing late filings (those occurring after January 1, 2025) of $149.</p>
                    <p>Corporate Transparency Act: <a href="https://www.congress.gov/bill/116th-congress/house-bill/2513" target="_blank">https://www.congress.gov/bill/116th-congress/house-bill/2513</a></p>
                    <p>FinCEN BOIR Information Page: <a href="https://fincen.gov/boi" target="_blank">https://fincen.gov/boi</a></p>
                </div>
                <div class="notice-box">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div class="box-title"></div>
                    <div class="box-content">
                        <p>In light of a recent federal court order, reporting companies are not currently required to file beneficial ownership information with FinCEN and are not subject to liability if they fail to do while the order remains in force. However, reporting companies may continue to voluntarily submit beneficial ownership information reports.</p>
                        <p>More information is available on FinCEN’s website at <a href="https://www.fincen.gov/boi" style="text-decoration:underline">https://www.fincen.gov/boi</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tabs-container">
        <div class="tab <?=$tab==1?'active':''?> <?=$tab>1?'completed':''?> tab-1" id="BOIRForm1">
            <div class="tab-fields-header">
                <div class="fields-title">Initial Details</div>
                <span class="fields-description">Details of individual completing this BOIR Filing.</span>
            </div>
            <div class="tab-fields">
                <div class="field col1-2">
                    <div class="field-title">Full Name*</div>
                    <input type="text" name="name" id="name" placeholder="Write Your Full Name" value="<?=$client_name;?>" <?=$current_form_status>0?'disabled':''?>>
                </div>
                <div class="field col1-2">
                    <div class="field-title">Email Address*</div>
                    <input type="email" name="email" id="email" placeholder="Write Your Email Address" value="<?=$client_email;?>" <?=$current_form_status>0?'disabled':''?>>
                </div>
                <div class="field">
                    <div class="field-title">Phone Number</div>
                    <input type="text" name="phone" id="phone" placeholder="Write Your Phone Number" value="<?=$client_phone;?>" <?=$current_form_status>0?'disabled':''?>>
                </div>
            </div>
            <hr class="tab-divider" />
            <div class="tab-fields-header">
                <div class="fields-title">Company Details</div>
                <span class="fields-description">Details of the company that is the subject of this BOIR Filing. Please include extension (e.g. LLC, Inc.)</span>
            </div>
            <div class="tab-fields">
                <div class="field">
                    <div class="field-title">Legal Name*</div>
                    <input type="text" name="legal_name" id="legal_name" placeholder="Write Full Name" value="<?=$initial_legal_name;?>">
                    <span class="info-ribbon-top"><i class="bi bi-info-circle-fill"></i><span>The legal name of the entity that is the subject of this filing.</span></span>
                </div>
                <div class="field">
                    <div class="field-title">Alternate Name (Trade Name, DBA)</div>
                    <textarea name="alternate_name" id="alternate_name" placeholder="DBA / Trade Names (each on a separate line)" rows="4" ><?=$initial_alternate_name;?></textarea>
                    <span class="info-ribbon-top"><i class="bi bi-info-circle-fill"></i><span>Any Doing Business As (DBA) or Trade Names associated with the entity.</span></span>
                </div>
                <div class="field col1-2">
                    <div class="field-title">Tax Identification Type*</div>
                    <select name="tax_type" id="tax_type" value="<?=$initial_tax_type;?>">
                        <option value="">Select ID Type</option>
                        <option value="EIN">EIN - Employer Identification Number</option>
                        <option value="SSN/ITIN">SSN or ITIN - Social Security Number / International Tax Identification Number</option>
                        <option value="Foreign">Foreign - Foreign Tax Identification Number</option>
                    </select>
                </div>
                <div class="field col1-2">
                    <div class="field-title">Tax Identification Number*</div>
                    <input type="text" name="tax_number" id="tax_number" placeholder="Write Your Tax Identification Number" value="<?=$initial_tax_number;?>">
                </div>
				
                <div class="form-buttons">
                    <button class="button-primary" id="boir_form1_submit">Continue to Filling <i class="bi bi-arrow-right"></i></button>
                    <span class="loader hidden" ></span>
                    <div class="error-message hidden"></div>
                </div>
                
            </div>
        </div>
        <div class="tab <?=$tab==2?'active':''?> <?=$tab>2?'completed':''?> tab-2">
            <div class="tab-steps">
                <div class="step <?=$step==1?'active':''?> <?=$step>1?'completed':''?>" data-step="1" id="BOIRForm2">
                    <div class="tab-fields">
                        <div class="field">
                            <div class="field-title">Country/Jurisdiction of Formation*</div>
                            <select name="juri_formation_country" id="juri_formation_country" value="<?=$juri_formation_country;?>">
                                <optgroup label="United States">
                                    <option value="United States">United States of America</option>
                                    <option value="American Samoa">American Samoa</option>
                                    <option value="Guam">Guam</option>
                                    <option value="Marshal Islands">Marshal Islands</option>
                                    <option value="Micronesia, Federated States">Micronesia, Federated States</option>
                                    <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                    <option value="Palau">Palau</option>
                                    <option value="U.S. Virgin Islands">U.S. Virgin Islands</option>
                                </optgroup>
                                <optgroup label="Rest of world">
                                    <option value="Puerto Rico">Puerto Rico</option>
                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Åland Islands">Åland Islands</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Anguilla">Anguilla</option>
                                    <option value="Antarctica">Antarctica</option>
                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Aruba">Aruba</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bermuda">Bermuda</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Bouvet Island">Bouvet Island</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                    <option value="Brunei Darussalam">Brunei Darussalam</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Cape Verde">Cape Verde</option>
                                    <option value="Cayman Islands">Cayman Islands</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Christmas Island">Christmas Island</option>
                                    <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo">Congo</option>
                                    <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                    <option value="Cook Islands">Cook Islands</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Cote D'ivoire">Cote D'ivoire</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czech Republic">Czech Republic</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                    <option value="Eritrea">Eritrea</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                    <option value="Faroe Islands">Faroe Islands</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="French Guiana">French Guiana</option>
                                    <option value="French Polynesia">French Polynesia</option>
                                    <option value="French Southern Territories">French Southern Territories</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Gibraltar">Gibraltar</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Greenland">Greenland</option>
                                    <option value="Grenada">Grenada</option>
                                    <option value="Guadeloupe">Guadeloupe</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guernsey">Guernsey</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guinea-bissau">Guinea-bissau</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                    <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hong Kong">Hong Kong</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Isle of Man">Isle of Man</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jersey">Jersey</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kiribati">Kiribati</option>
                                    <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                    <option value="Korea, Republic of">Korea, Republic of</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                    <option value="Liechtenstein">Liechtenstein</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Macao">Macao</option>
                                    <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Martinique">Martinique</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mayotte">Mayotte</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Moldova, Republic of">Moldova, Republic of</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montenegro">Montenegro</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Nauru">Nauru</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="Netherlands Antilles">Netherlands Antilles</option>
                                    <option value="New Caledonia">New Caledonia</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Niue">Niue</option>
                                    <option value="Norfolk Island">Norfolk Island</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Pitcairn">Pitcairn</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Reunion">Reunion</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russian Federation">Russian Federation</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Saint Helena">Saint Helena</option>
                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                    <option value="Saint Lucia">Saint Lucia</option>
                                    <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                    <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                    <option value="Samoa">Samoa</option>
                                    <option value="San Marino">San Marino</option>
                                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Solomon Islands">Solomon Islands</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                    <option value="Swaziland">Swaziland</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Timor-leste">Timor-leste</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Tokelau">Tokelau</option>
                                    <option value="Tonga">Tonga</option>
                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                    <option value="Tuvalu">Tuvalu</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>

                                    <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Vanuatu">Vanuatu</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Viet Nam">Viet Nam</option>
                                    <option value="Virgin Islands, British">Virgin Islands, British</option>
                                    <option value="Wallis and Futuna">Wallis and Futuna</option>
                                    <option value="Western Sahara">Western Sahara</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <hr class="tab-divider" />
                    <div class="tab-fields-header">
                        <div class="fields-title"><span id="juri_formation_country_type">Domestic</span> Reporting Company</div>
                        <span class="fields-description">Please provide either the US state where the entity was first registered or the foreign tribal jurisdiction where the entity was first registered.</span>
                    </div>
                    <div class="tab-fields has-or-divider-group1">
                        <div class="field col1-2 has-or-divider">
                            <div class="field-title">State of Formation*</div>
                            <select name="juri_formation_state" id="juri_formation_state" value="<?=$juri_formation_state;?>" >
							    <option value="">Please Select a Value</option>
						        <option value="Alabama">Alabama</option>
                                <option value="Alaska">Alaska</option>
                                <option value="American Samoa">American Samoa</option>
                                <option value="Arizona">Arizona</option>
                                <option value="Arkansas">Arkansas</option>
                                <option value="California">California</option>
                                <option value="Colorado">Colorado</option>
                                <option value="Connecticut">Connecticut</option>
                                <option value="Delaware">Delaware</option>
                                <option value="District of Columbia">District of Columbia</option>
                                <option value="Florida">Florida</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Guam">Guam</option>
                                <option value="Hawaii">Hawaii</option>
                                <option value="Idaho">Idaho</option>
                                <option value="Illinois">Illinois</option>
                                <option value="Indiana">Indiana</option>
                                <option value="Iowa">Iowa</option>
                                <option value="Kansas">Kansas</option>
                                <option value="Kentucky">Kentucky</option>
                                <option value="Louisiana">Louisiana</option>
                                <option value="Maine">Maine</option>
                                <option value="Maryland">Maryland</option>
                                <option value="Massachusetts">Massachusetts</option>
                                <option value="Michigan">Michigan</option>
                                <option value="Minnesota">Minnesota</option>
                                <option value="Mississippi">Mississippi</option>
                                <option value="Missouri">Missouri</option>
                                <option value="Montana">Montana</option>
                                <option value="Nebraska">Nebraska</option>
                                <option value="Nevada">Nevada</option>
                                <option value="New Hampshire">New Hampshire</option>
                                <option value="New Jersey">New Jersey</option>
                                <option value="New Mexico">New Mexico</option>
                                <option value="New York">New York</option>
                                <option value="North Carolina">North Carolina</option>
                                <option value="North Dakota">North Dakota</option>
                                <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                <option value="Ohio">Ohio</option>
                                <option value="Oklahoma">Oklahoma</option>
                                <option value="Oregon">Oregon</option>
                                <option value="Pennsylvania">Pennsylvania</option>
                                <option value="Puerto Rico">Puerto Rico</option>
                                <option value="Rhode Island">Rhode Island</option>
                                <option value="South Carolina">South Carolina</option>
                                <option value="South Dakota">South Dakota</option>
                                <option value="Tennessee">Tennessee</option>
                                <option value="Texas">Texas</option>
                                <option value="U.S. Minor Outlying Islands">U.S. Minor Outlying Islands</option>
                                <option value="Utah">Utah</option>
                                <option value="Vermont">Vermont</option>
                                <option value="Virgin Islands of the U.S.">Virgin Islands of the U.S.</option>
                                <option value="Virginia">Virginia</option>
                                <option value="Washington">Washington</option>
                                <option value="West Virginia">West Virginia</option>
                                <option value="Wisconsin">Wisconsin</option>
                                <option value="Wyoming">Wyoming</option>
                            </select>
                        </div>
                        <div class="field or-divider">
                            OR
                        </div>
                        <div class="field col1-2 has-or-divider">
                            <div class="field-title">Tribal jurisdiction of formation</div>
                            <input type="text" name="juri_tribal" id="juri_tribal" placeholder="Write Tribal Jurisdiction of Formation" value="<?=$juri_tribal;?>">
                        </div>
                    </div>
                    <hr class="tab-divider" />
                    <div class="tab-fields-header">
                        <div class="fields-title">Current U.S. Address</div>
                        <span class="fields-description">Details of the current U.S. address that is the subject of this BOIR Filing.</span>
                    </div>
                    <div class="tab-fields">
                        <div class="field">
                            <div class="field-title">Address*</div>
                            <input type="text" name="juri_address_line_1" id="juri_address_line_1" placeholder="Address Line 1" value="<?=$juri_address_line_1;?>">
                        </div>
                        <div class="field">
                            <div class="field-title">Apartment, suite, etc.</div>
                            <input type="text" name="juri_address_line_2" id="juri_address_line_2" placeholder="Address Line 2" value="<?=$juri_address_line_2;?>">
                        </div>
                        <div class="field col1-3">
                            <div class="field-title">City*</div>
                            <input type="text" name="juri_city" id="juri_city" placeholder="Write Name of the City" value="<?=$juri_city;?>">
                        </div>
                        <div class="field col1-3">
                            <div class="field-title">State/Province*</div>
                            <select name="juri_state" id="juri_state" value="<?=$juri_state;?>">
							    <option value="">Select a Value</option>
						        <option value="Alabama">Alabama</option>
                                <option value="Alaska">Alaska</option>
                                <option value="American Samoa">American Samoa</option>
                                <option value="Arizona">Arizona</option>
                                <option value="Arkansas">Arkansas</option>
                                <option value="California">California</option>
                                <option value="Colorado">Colorado</option>
                                <option value="Connecticut">Connecticut</option>
                                <option value="Delaware">Delaware</option>
                                <option value="District of Columbia">District of Columbia</option>
                                <option value="Florida">Florida</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Guam">Guam</option>
                                <option value="Hawaii">Hawaii</option>
                                <option value="Idaho">Idaho</option>
                                <option value="Illinois">Illinois</option>
                                <option value="Indiana">Indiana</option>
                                <option value="Iowa">Iowa</option>
                                <option value="Kansas">Kansas</option>
                                <option value="Kentucky">Kentucky</option>
                                <option value="Louisiana">Louisiana</option>
                                <option value="Maine">Maine</option>
                                <option value="Maryland">Maryland</option>
                                <option value="Massachusetts">Massachusetts</option>
                                <option value="Michigan">Michigan</option>
                                <option value="Minnesota">Minnesota</option>
                                <option value="Mississippi">Mississippi</option>
                                <option value="Missouri">Missouri</option>
                                <option value="Montana">Montana</option>
                                <option value="Nebraska">Nebraska</option>
                                <option value="Nevada">Nevada</option>
                                <option value="New Hampshire">New Hampshire</option>
                                <option value="New Jersey">New Jersey</option>
                                <option value="New Mexico">New Mexico</option>
                                <option value="New York">New York</option>
                                <option value="North Carolina">North Carolina</option>
                                <option value="North Dakota">North Dakota</option>
                                <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                <option value="Ohio">Ohio</option>
                                <option value="Oklahoma">Oklahoma</option>
                                <option value="Oregon">Oregon</option>
                                <option value="Pennsylvania">Pennsylvania</option>
                                <option value="Puerto Rico">Puerto Rico</option>
                                <option value="Rhode Island">Rhode Island</option>
                                <option value="South Carolina">South Carolina</option>
                                <option value="South Dakota">South Dakota</option>
                                <option value="Tennessee">Tennessee</option>
                                <option value="Texas">Texas</option>
                                <option value="U.S. Minor Outlying Islands">U.S. Minor Outlying Islands</option>
                                <option value="Utah">Utah</option>
                                <option value="Vermont">Vermont</option>
                                <option value="Virgin Islands of the U.S.">Virgin Islands of the U.S.</option>
                                <option value="Virginia">Virginia</option>
                                <option value="Washington">Washington</option>
                                <option value="West Virginia">West Virginia</option>
                                <option value="Wisconsin">Wisconsin</option>
                                <option value="Wyoming">Wyoming</option>
                            </select>
                        </div>
                        <div class="field col1-3">
                            <div class="field-title">Zip/Postal Code*</div>
                            <input type="text" name="juri_zip" id="juri_zip" placeholder="Write Zip/Postal Code" value="<?=$juri_zip;?>">
                        </div>
						
															                    <div class="form-buttons">
                        <button class="button-primary" id="back_btn" data-payment-type="2">Back</button>
                    </div>
                        <div class="form-buttons">
                            <button class="button-primary" id="boir_form2_submit">Continue to Applicants <i class="bi bi-arrow-right"></i></button>
                            <span class="loader hidden" ></span>
                            <div class="error-message hidden"></div>
                        </div>
                    </div>
                </div>
                <div class="step <?=$step==2?'active':''?> <?=$step>2?'completed':''?>" data-step="2" id="BOIRForm3">
                    <div class="tab-fields-header">
                        
                        <div class="fields-description">
                            <div class="margin:10px 0px;">Only companies registered on or after 1st January, 2024 are required to file company applicant information. A reporting company can have up to two company applicants:</div>
                            <ul style="margin:10px 0px;list-style-type:number">
                                <li>The individual who directly files the document that creates or registers the company</li>
                                <li>The individual who is primarily responsible for directing or controlling the filing</li>
                            </ul>

                        </div>
                    </div>
                    <div class="tab-fields">
                        <div class="field">
                            <div class="field-title">Was the reporting company filed on or after January 1st, 2024?*</div>
                            <div class="field-radio">
                                <label class="radio" for="existing_company1">
                                    <input type="radio" name="existing_company[]" id="existing_company1" value="yes" <?=($existing_reporting_company=='yes')?'checked':''?>>
                                    <label for="existing_company1">Yes</label>
                                </label>
                                <label class="radio" for="existing_company0">
                                    <input type="radio" name="existing_company[]" id="existing_company0" value="no" <?=($existing_reporting_company=='no')?'checked':''?>>
                                    <label for="existing_company0">No</label>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="tab-fields no_company_applicants_msg <?=$existing_reporting_company!='no'?'hidden':''?>">
                        <div class="field" >
                            <div class="field-description">Companies registered before January 1st, 2024 are not required to file company applicant information. You may proceed to the next step.</div>
                        </div>
                    </div>
                    <div class="form-card company_applicants_form <?=$existing_reporting_company!='yes'?'hidden':''?>" id="BOIRForm3x">
                        <div class="tab-fields-header">
                            <div class="fields-title">Company Applicant Information</div>
                            <div class="fields-description">Companies registered on or after January 1st, 2024 are required to file company applicant information.</div>
                        </div>
                        <div class="tab-fields">
                            <div class="field col1-3">
                                <div class="field-title">Last Name*</div>
                                <input type="text" name="applicant_last_name" id="applicant_last_name" placeholder="Write Last Name">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">First Name*</div>
                                <input type="text" name="applicant_first_name" id="applicant_first_name" placeholder="Write First Name">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">Middle Name</div>
                                <input type="text" name="applicant_middle_name" id="applicant_middle_name" placeholder="Write Middle Name">
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">Suffix</div>
                                <input type="text" name="applicant_suffix" id="applicant_suffix" placeholder="Write Suffix">
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">Date of Birth*</div>
                                <input type="date" name="applicant_dob" id="applicant_dob" placeholder="Write Date of Birth">
                            </div>
                        </div>
                        <div class="tab-fields-header">
                            <div class="sub-fields-title">Current Address</div>
                            <div class="sub-fields-description">Current address of the company applicant.</div>
                        </div>
                        <div class="tab-fields">
                            <div class="field">
                                <div class="field-title">What type of address is this?*</div>
                                <select name="applicant_address_type" id="applicant_address_type">
                                    <option value="">Select an option</option>
                                    <option value="business">Business Address</option>
                                    <option value="residential">Residential Address</option>
                                </select>
                            </div>
                            <div class="field col2-3">
                                <div class="field-title">Street Address*</div>
                                <input type="text" name="applicant_address" id="applicant_address" placeholder="Write Street Address">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">City*</div>
                                <input type="text" name="applicant_city" id="applicant_city" placeholder="Write Name of the City">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">Country*</div>
                                <select name="applicant_country" id="applicant_country">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="United States">United States of America</option>
                                        <option value="American Samoa">American Samoa</option>
                                        <option value="Guam">Guam</option>
                                        <option value="Marshal Islands">Marshal Islands</option>
                                        <option value="Micronesia, Federated States">Micronesia, Federated States</option>
                                        <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                        <option value="Palau">Palau</option>
                                        <option value="U.S. Virgin Islands">U.S. Virgin Islands</option>
                                    </optgroup>
                                    <optgroup label="Rest of world">
                                        <option value="Puerto Rico">Puerto Rico</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Åland Islands">Åland Islands</option>
                                        <option value="Albania">Albania</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="Andorra">Andorra</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Anguilla">Anguilla</option>
                                        <option value="Antarctica">Antarctica</option>
                                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Aruba">Aruba</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Austria">Austria</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahamas">Bahamas</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Barbados">Barbados</option>
                                        <option value="Belarus">Belarus</option>
                                        <option value="Belgium">Belgium</option>
                                        <option value="Belize">Belize</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Bermuda">Bermuda</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Bolivia">Bolivia</option>
                                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Bouvet Island">Bouvet Island</option>
                                        <option value="Brazil">Brazil</option>
                                        <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                        <option value="Brunei Darussalam">Brunei Darussalam</option>
                                        <option value="Bulgaria">Bulgaria</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Cayman Islands">Cayman Islands</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Chile">Chile</option>
                                        <option value="China">China</option>
                                        <option value="Christmas Island">Christmas Island</option>
                                        <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                        <option value="Colombia">Colombia</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                        <option value="Cook Islands">Cook Islands</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Cote D'ivoire">Cote D'ivoire</option>
                                        <option value="Croatia">Croatia</option>
                                        <option value="Cuba">Cuba</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="Czech Republic">Czech Republic</option>
                                        <option value="Denmark">Denmark</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Dominica">Dominica</option>
                                        <option value="Dominican Republic">Dominican Republic</option>
                                        <option value="Ecuador">Ecuador</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="El Salvador">El Salvador</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Estonia">Estonia</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                        <option value="Faroe Islands">Faroe Islands</option>
                                        <option value="Fiji">Fiji</option>
                                        <option value="Finland">Finland</option>
                                        <option value="France">France</option>
                                        <option value="French Guiana">French Guiana</option>
                                        <option value="French Polynesia">French Polynesia</option>
                                        <option value="French Southern Territories">French Southern Territories</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Gibraltar">Gibraltar</option>
                                        <option value="Greece">Greece</option>
                                        <option value="Greenland">Greenland</option>
                                        <option value="Grenada">Grenada</option>
                                        <option value="Guadeloupe">Guadeloupe</option>
                                        <option value="Guatemala">Guatemala</option>
                                        <option value="Guernsey">Guernsey</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-bissau">Guinea-bissau</option>
                                        <option value="Guyana">Guyana</option>
                                        <option value="Haiti">Haiti</option>
                                        <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                        <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                        <option value="Honduras">Honduras</option>
                                        <option value="Hong Kong">Hong Kong</option>
                                        <option value="Hungary">Hungary</option>
                                        <option value="Iceland">Iceland</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Ireland">Ireland</option>
                                        <option value="Isle of Man">Isle of Man</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Jamaica">Jamaica</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jersey">Jersey</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Kiribati">Kiribati</option>
                                        <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                        <option value="Korea, Republic of">Korea, Republic of</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                        <option value="Latvia">Latvia</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                        <option value="Liechtenstein">Liechtenstein</option>
                                        <option value="Lithuania">Lithuania</option>
                                        <option value="Luxembourg">Luxembourg</option>
                                        <option value="Macao">Macao</option>
                                        <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Malta">Malta</option>
                                        <option value="Martinique">Martinique</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Mayotte">Mayotte</option>
                                        <option value="Mexico">Mexico</option>
                                        <option value="Moldova, Republic of">Moldova, Republic of</option>
                                        <option value="Monaco">Monaco</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Montenegro">Montenegro</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Nauru">Nauru</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="Netherlands">Netherlands</option>
                                        <option value="Netherlands Antilles">Netherlands Antilles</option>
                                        <option value="New Caledonia">New Caledonia</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="Nicaragua">Nicaragua</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Niue">Niue</option>
                                        <option value="Norfolk Island">Norfolk Island</option>
                                        <option value="Norway">Norway</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                        <option value="Panama">Panama</option>
                                        <option value="Papua New Guinea">Papua New Guinea</option>
                                        <option value="Paraguay">Paraguay</option>
                                        <option value="Peru">Peru</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Pitcairn">Pitcairn</option>
                                        <option value="Poland">Poland</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Reunion">Reunion</option>
                                        <option value="Romania">Romania</option>
                                        <option value="Russian Federation">Russian Federation</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Saint Helena">Saint Helena</option>
                                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                        <option value="Saint Lucia">Saint Lucia</option>
                                        <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                        <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                        <option value="Samoa">Samoa</option>
                                        <option value="San Marino">San Marino</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Serbia">Serbia</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra Leone">Sierra Leone</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Slovakia">Slovakia</option>
                                        <option value="Slovenia">Slovenia</option>
                                        <option value="Solomon Islands">Solomon Islands</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                        <option value="Spain">Spain</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Suriname">Suriname</option>
                                        <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Sweden">Sweden</option>
                                        <option value="Switzerland">Switzerland</option>
                                        <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                        <option value="Taiwan">Taiwan</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Timor-leste">Timor-leste</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tokelau">Tokelau</option>
                                        <option value="Tonga">Tonga</option>
                                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                        <option value="Tuvalu">Tuvalu</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="United Kingdom">United Kingdom</option>

                                        <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                        <option value="Uruguay">Uruguay</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vanuatu">Vanuatu</option>
                                        <option value="Venezuela">Venezuela</option>
                                        <option value="Viet Nam">Viet Nam</option>
                                        <option value="Virgin Islands, British">Virgin Islands, British</option>
                                        <option value="Wallis and Futuna">Wallis and Futuna</option>
                                        <option value="Western Sahara">Western Sahara</option>
                                        <option value="Yemen">Yemen</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">State*</div>
                                <select name="applicant_state" id="applicant_state">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="AL">Alabama</option>
                                        <option value="AK">Alaska</option>
                                        <option value="AZ">Arizona</option>
                                        <option value="AR">Arkansas</option>
                                        <option value="CA">California</option>
                                        <option value="CO">Colorado</option>
                                        <option value="CT">Connecticut</option>
                                        <option value="DE">Delaware</option>
                                        <option value="DC">District Of Columbia</option>
                                        <option value="FL">Florida</option>
                                        <option value="GA">Georgia</option>
                                        <option value="HI">Hawaii</option>
                                        <option value="ID">Idaho</option>
                                        <option value="IL">Illinois</option>
                                        <option value="IN">Indiana</option>
                                        <option value="IA">Iowa</option>
                                        <option value="KS">Kansas</option>
                                        <option value="KY">Kentucky</option>
                                        <option value="LA">Louisiana</option>
                                        <option value="ME">Maine</option>
                                        <option value="MD">Maryland</option>
                                        <option value="MA">Massachusetts</option>
                                        <option value="MI">Michigan</option>
                                        <option value="MN">Minnesota</option>
                                        <option value="MS">Mississippi</option>
                                        <option value="MO">Missouri</option>
                                        <option value="MT">Montana</option>
                                        <option value="NE">Nebraska</option>
                                        <option value="NV">Nevada</option>
                                        <option value="NH">New Hampshire</option>
                                        <option value="NJ">New Jersey</option>
                                        <option value="NM">New Mexico</option>
                                        <option value="NY">New York</option>
                                        <option value="NC">North Carolina</option>
                                        <option value="ND">North Dakota</option>
                                        <option value="OH">Ohio</option>
                                        <option value="OK">Oklahoma</option>
                                        <option value="OR">Oregon</option>
                                        <option value="PA">Pennsylvania</option>
                                        <option value="RI">Rhode Island</option>
                                        <option value="SC">South Carolina</option>
                                        <option value="SD">South Dakota</option>
                                        <option value="TN">Tennessee</option>
                                        <option value="TX">Texas</option>
                                        <option value="UT">Utah</option>
                                        <option value="VT">Vermont</option>
                                        <option value="VA">Virginia</option>
                                        <option value="WA">Washington</option>
                                        <option value="WV">West Virginia</option>
                                        <option value="WI">Wisconsin</option>
                                        <option value="WY">Wyoming</option>
                                    </optgroup>

                                    <optgroup label="US Outlying Territories">
                                        <option value="AS">American Samoa</option>
                                        <option value="GU">Guam</option>
                                        <option value="MP">Northern Mariana Islands</option>
                                        <option value="PR">Puerto Rico</option>
                                        <option value="UM">United States Minor Outlying Islands</option>
                                        <option value="VI">Virgin Islands</option>
                                    </optgroup>

                                    <optgroup label="US Armed Forces">
                                        <option value="AA">Armed Forces Americas</option>
                                        <option value="AP">Armed Forces Pacific</option>
                                        <option value="AE">Armed Forces Others</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">Zip Code*</div>
                                <input type="text" name="applicant_zip" id="applicant_zip" placeholder="Write Zip Code">
                            </div>
                        </div>
                        <div class="tab-fields-header">
                            <div class="sub-fields-title">Identity Verification</div>
                            <div class="sub-fields-description">Identity information of the company applicant.</div>
                        </div>
                        <div class="tab-fields">
                            <div class="field">
                                <div class="field-title">Upload image of ID (.png, .jpg, .pdf)*</div>
                                <input type="file" name="applicant_id_image" id="applicant_id_image" placeholder="Add ID Image" accept="image/png, image/jpeg, image/jpg, application/pdf">
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">What is the ID Type?*</div>
                                <select name="applicant_id_type" id="applicant_id_type">
                                    <option value="">Select an option</option>
                                    <option value="state_drivers_license">State Issued Driver's License</option>
                                    <option value="state_local_id">State/local/tribal-issued ID</option>
                                    <option value="us_passport">U.S. Passport</option>
                                    <option value="foreign_passport">Foreign Passport</option>
                                </select>
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">ID Number*</div>
                                <input type="text" name="applicant_id_number" id="applicant_id_number" placeholder="Write ID Number" maxlength="18">
                            </div>
                            <div class="field">
                                <div class="field-title">Country*</div>
                                <select name="applicant_id_country" id="applicant_id_country">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="United States">United States of America</option>
                                        <option value="American Samoa">American Samoa</option>
                                        <option value="Guam">Guam</option>
                                        <option value="Marshal Islands">Marshal Islands</option>
                                        <option value="Micronesia, Federated States">Micronesia, Federated States</option>
                                        <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                        <option value="Palau">Palau</option>
                                        <option value="U.S. Virgin Islands">U.S. Virgin Islands</option>
                                    </optgroup>
                                    <optgroup label="Rest of world">
                                        <option value="Puerto Rico">Puerto Rico</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Åland Islands">Åland Islands</option>
                                        <option value="Albania">Albania</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="Andorra">Andorra</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Anguilla">Anguilla</option>
                                        <option value="Antarctica">Antarctica</option>
                                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Aruba">Aruba</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Austria">Austria</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahamas">Bahamas</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Barbados">Barbados</option>
                                        <option value="Belarus">Belarus</option>
                                        <option value="Belgium">Belgium</option>
                                        <option value="Belize">Belize</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Bermuda">Bermuda</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Bolivia">Bolivia</option>
                                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Bouvet Island">Bouvet Island</option>
                                        <option value="Brazil">Brazil</option>
                                        <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                        <option value="Brunei Darussalam">Brunei Darussalam</option>
                                        <option value="Bulgaria">Bulgaria</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Cayman Islands">Cayman Islands</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Chile">Chile</option>
                                        <option value="China">China</option>
                                        <option value="Christmas Island">Christmas Island</option>
                                        <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                        <option value="Colombia">Colombia</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                        <option value="Cook Islands">Cook Islands</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Cote D'ivoire">Cote D'ivoire</option>
                                        <option value="Croatia">Croatia</option>
                                        <option value="Cuba">Cuba</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="Czech Republic">Czech Republic</option>
                                        <option value="Denmark">Denmark</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Dominica">Dominica</option>
                                        <option value="Dominican Republic">Dominican Republic</option>
                                        <option value="Ecuador">Ecuador</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="El Salvador">El Salvador</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Estonia">Estonia</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                        <option value="Faroe Islands">Faroe Islands</option>
                                        <option value="Fiji">Fiji</option>
                                        <option value="Finland">Finland</option>
                                        <option value="France">France</option>
                                        <option value="French Guiana">French Guiana</option>
                                        <option value="French Polynesia">French Polynesia</option>
                                        <option value="French Southern Territories">French Southern Territories</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Gibraltar">Gibraltar</option>
                                        <option value="Greece">Greece</option>
                                        <option value="Greenland">Greenland</option>
                                        <option value="Grenada">Grenada</option>
                                        <option value="Guadeloupe">Guadeloupe</option>
                                        <option value="Guatemala">Guatemala</option>
                                        <option value="Guernsey">Guernsey</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-bissau">Guinea-bissau</option>
                                        <option value="Guyana">Guyana</option>
                                        <option value="Haiti">Haiti</option>
                                        <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                        <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                        <option value="Honduras">Honduras</option>
                                        <option value="Hong Kong">Hong Kong</option>
                                        <option value="Hungary">Hungary</option>
                                        <option value="Iceland">Iceland</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Ireland">Ireland</option>
                                        <option value="Isle of Man">Isle of Man</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Jamaica">Jamaica</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jersey">Jersey</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Kiribati">Kiribati</option>
                                        <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                        <option value="Korea, Republic of">Korea, Republic of</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                        <option value="Latvia">Latvia</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                        <option value="Liechtenstein">Liechtenstein</option>
                                        <option value="Lithuania">Lithuania</option>
                                        <option value="Luxembourg">Luxembourg</option>
                                        <option value="Macao">Macao</option>
                                        <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Malta">Malta</option>
                                        <option value="Martinique">Martinique</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Mayotte">Mayotte</option>
                                        <option value="Mexico">Mexico</option>
                                        <option value="Moldova, Republic of">Moldova, Republic of</option>
                                        <option value="Monaco">Monaco</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Montenegro">Montenegro</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Nauru">Nauru</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="Netherlands">Netherlands</option>
                                        <option value="Netherlands Antilles">Netherlands Antilles</option>
                                        <option value="New Caledonia">New Caledonia</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="Nicaragua">Nicaragua</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Niue">Niue</option>
                                        <option value="Norfolk Island">Norfolk Island</option>
                                        <option value="Norway">Norway</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                        <option value="Panama">Panama</option>
                                        <option value="Papua New Guinea">Papua New Guinea</option>
                                        <option value="Paraguay">Paraguay</option>
                                        <option value="Peru">Peru</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Pitcairn">Pitcairn</option>
                                        <option value="Poland">Poland</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Reunion">Reunion</option>
                                        <option value="Romania">Romania</option>
                                        <option value="Russian Federation">Russian Federation</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Saint Helena">Saint Helena</option>
                                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                        <option value="Saint Lucia">Saint Lucia</option>
                                        <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                        <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                        <option value="Samoa">Samoa</option>
                                        <option value="San Marino">San Marino</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Serbia">Serbia</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra Leone">Sierra Leone</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Slovakia">Slovakia</option>
                                        <option value="Slovenia">Slovenia</option>
                                        <option value="Solomon Islands">Solomon Islands</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                        <option value="Spain">Spain</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Suriname">Suriname</option>
                                        <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Sweden">Sweden</option>
                                        <option value="Switzerland">Switzerland</option>
                                        <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                        <option value="Taiwan">Taiwan</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Timor-leste">Timor-leste</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tokelau">Tokelau</option>
                                        <option value="Tonga">Tonga</option>
                                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                        <option value="Tuvalu">Tuvalu</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="United Kingdom">United Kingdom</option>

                                        <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                        <option value="Uruguay">Uruguay</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vanuatu">Vanuatu</option>
                                        <option value="Venezuela">Venezuela</option>
                                        <option value="Viet Nam">Viet Nam</option>
                                        <option value="Virgin Islands, British">Virgin Islands, British</option>
                                        <option value="Wallis and Futuna">Wallis and Futuna</option>
                                        <option value="Western Sahara">Western Sahara</option>
                                        <option value="Yemen">Yemen</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="tab-fields has-or-divider-group2">
                            <div class="field col1-2 has-or-divider">
                                <div class="field-title">State</div>
                                <select name="applicant_id_state" id="applicant_id_state">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="AL">Alabama</option>
                                        <option value="AK">Alaska</option>
                                        <option value="AZ">Arizona</option>
                                        <option value="AR">Arkansas</option>
                                        <option value="CA">California</option>
                                        <option value="CO">Colorado</option>
                                        <option value="CT">Connecticut</option>
                                        <option value="DE">Delaware</option>
                                        <option value="DC">District Of Columbia</option>
                                        <option value="FL">Florida</option>
                                        <option value="GA">Georgia</option>
                                        <option value="HI">Hawaii</option>
                                        <option value="ID">Idaho</option>
                                        <option value="IL">Illinois</option>
                                        <option value="IN">Indiana</option>
                                        <option value="IA">Iowa</option>
                                        <option value="KS">Kansas</option>
                                        <option value="KY">Kentucky</option>
                                        <option value="LA">Louisiana</option>
                                        <option value="ME">Maine</option>
                                        <option value="MD">Maryland</option>
                                        <option value="MA">Massachusetts</option>
                                        <option value="MI">Michigan</option>
                                        <option value="MN">Minnesota</option>
                                        <option value="MS">Mississippi</option>
                                        <option value="MO">Missouri</option>
                                        <option value="MT">Montana</option>
                                        <option value="NE">Nebraska</option>
                                        <option value="NV">Nevada</option>
                                        <option value="NH">New Hampshire</option>
                                        <option value="NJ">New Jersey</option>
                                        <option value="NM">New Mexico</option>
                                        <option value="NY">New York</option>
                                        <option value="NC">North Carolina</option>
                                        <option value="ND">North Dakota</option>
                                        <option value="OH">Ohio</option>
                                        <option value="OK">Oklahoma</option>
                                        <option value="OR">Oregon</option>
                                        <option value="PA">Pennsylvania</option>
                                        <option value="RI">Rhode Island</option>
                                        <option value="SC">South Carolina</option>
                                        <option value="SD">South Dakota</option>
                                        <option value="TN">Tennessee</option>
                                        <option value="TX">Texas</option>
                                        <option value="UT">Utah</option>
                                        <option value="VT">Vermont</option>
                                        <option value="VA">Virginia</option>
                                        <option value="WA">Washington</option>
                                        <option value="WV">West Virginia</option>
                                        <option value="WI">Wisconsin</option>
                                        <option value="WY">Wyoming</option>
                                    </optgroup>

                                    <optgroup label="US Outlying Territories">
                                        <option value="AS">American Samoa</option>
                                        <option value="GU">Guam</option>
                                        <option value="MP">Northern Mariana Islands</option>
                                        <option value="PR">Puerto Rico</option>
                                        <option value="UM">United States Minor Outlying Islands</option>
                                        <option value="VI">Virgin Islands</option>
                                    </optgroup>

                                    <optgroup label="US Armed Forces">
                                        <option value="AA">Armed Forces Americas</option>
                                        <option value="AP">Armed Forces Pacific</option>
                                        <option value="AE">Armed Forces Others</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="field or-divider">
                                OR
                            </div>
                            <div class="field col1-2 has-or-divider">
                                <div class="field-title">Tribal Jurisdiction of Registration</div>
                                <input type="text" name="applicant_id_tribal_jurisdiction" id="applicant_id_tribal_jurisdiction" placeholder="Write Tribal Jurisdiction of Registration">
                            </div>
                        </div>
                        <div class="tab-fields">
                            <div class="form-buttons">
                                <button class="button-primary" id="boir_form3x_submit"><i class="bi bi-plus-lg"></i> Add Applicant</button>
                                <span class="loader hidden" ></span>
                                <div class="error-message hidden" id='add_applicant_error'></div>
                            </div>
                        </div>
                    </div>
                    <div class="data-card company_applicants_form <?=$existing_reporting_company!='yes'?'hidden':''?>" data-id="1" data-found="<?=$fillings_company_applicants_status;?>">

                    </div>
                    <div class="tab-fields">
															                    <div class="form-buttons">
                        <button class="button-primary" id="back_btn" data-payment-type="3">Back</button>
                    </div>
                        <div class="form-buttons">
                            <button class="button-primary" id="boir_form3_submit">Continue to Owners <i class="bi bi-arrow-right"></i></button>
                            <span class="loader hidden" ></span>
                            <div class="error-message hidden"></div>
                        </div>
                    </div>
                </div>
                <div class="step <?=$step==3?'active':''?> <?=$step>3?'completed':''?>" data-step="3" id="BOIRForm4">
                    <div class="tab-fields-header">
                        
                        <div class="fields-description">
                            <div class="margin:10px 0px;">All companies are required to report beneficial owner information. A reporting company can have any number of beneficial owners. A beneficial owner is:</div>
                            <ul style="margin:10px 0px;list-style-type:number">
                                <li>Someone who exercises substantial control over the reporting company.</li>
                                <li>Someone who owns or controls at least 25% of the reporting company's ownership interests.</li>
                                <li>Beneficial owners must be individuals; corporations, trusts, and other legal entities are not considered to be beneficial owners.</li>
                            </ul>
                            <div style="color:#7a7a7a;margin:10px 0px;" >Note: You must submit <span style="color:#000;font-weight:bold">all</span> of the reporting company's beneficial owners.</div>
                        </div>
                    </div>
                    <div class="form-card beneficial_owners_form" id="BOIRForm4x">
                        <div class="tab-fields-header">
                            <div class="fields-title">Beneficial Owner Information</div>
                            <div class="fields-description">All companies are required to file beneficial ownership information.</div>
                        </div>
                        <div class="special-box">
                            <label class="box-checkbox" for="owner_is_minor">
                                <input class="checkbox" type="checkbox" name="owner_is_minor" id="owner_is_minor" value="1">
                                <label for="owner_is_minor">Parent/Guardian information instead of minor child</label>
                            </label>
                            <p class="box-content" style="margin: 20px 0px;">Check this box if you are submitting parent/guardian information instead of a minor child’s information.</p>
                        </div>
                        <div class="special-box" style="margin-top: 20px;">
                            <label class="box-checkbox" for="owner_exemption">
                                <input class="checkbox" type="checkbox" name="owner_exemption" id="owner_exemption" value="1">
                                <label for="owner_exemption" class="no-padding" >Exempt Entity</label>
                            </label>
                            <div style="margin: 20px 0px;">
                                <p class="box-content">Check this box if the beneficial owner holds its ownership interest in the reporting company exclusively through one or more exempt entities, and the name of that exempt entity or entities are being reported in lieu of the beneficial owner’s information. If checked, provide the legal name of the exempt entity in the next field.</p>
                                <p class="box-content">By checking this box, you’re indicating that instead of providing the beneficial owner’s personal information, you will report the legal name(s) of the exempt entity or entities that hold their ownership interest. For example, if the beneficial owner holds their stake through a publicly traded company, you would list the company’s name rather than the owner’s details.</p>
                            </div>
                        </div>
                        <div class="tab-fields">
                            <div class="field col1-3">
                                <div class="field-title">Last Name (or Legal Name)*</div>
                                <input type="text" name="owner_last_name" id="owner_last_name" placeholder="Write Last Name">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">First Name*</div>
                                <input type="text" name="owner_first_name" id="owner_first_name" placeholder="Write First Name">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">Middle Name</div>
                                <input type="text" name="owner_middle_name" id="owner_middle_name" placeholder="Write Middle Name">
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">Suffix</div>
                                <input type="text" name="owner_suffix" id="owner_suffix" placeholder="Write Suffix">
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">Date of Birth*</div>
                                <input type="date" name="owner_dob" id="owner_dob" placeholder="Write Date of Birth">
                            </div>
                        </div>
                        <div class="tab-fields-header">
                            <div class="sub-fields-title">Current Address</div>
                            <div class="sub-fields-description">Current address of the beneficial owner.</div>
                        </div>
                        <div class="tab-fields">
                            <div class="field">
                                <div class="field-title">What type of address is this?*</div>
                                <select name="owner_address_type" id="owner_address_type">
                                    <option value="">Select an option</option>
                                    <option value="business">Business Address</option>
                                    <option value="residential">Residential Address</option>
                                </select>
                            </div>
                            <div class="field col2-3">
                                <div class="field-title">Street Address*</div>
                                <input type="text" name="owner_address" id="owner_address" placeholder="Write Street Address">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">City*</div>
                                <input type="text" name="owner_city" id="owner_city" placeholder="Write Name of the City">
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">Country*</div>
                                <select name="owner_country" id="owner_country">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="United States">United States of America</option>
                                        <option value="American Samoa">American Samoa</option>
                                        <option value="Guam">Guam</option>
                                        <option value="Marshal Islands">Marshal Islands</option>
                                        <option value="Micronesia, Federated States">Micronesia, Federated States</option>
                                        <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                        <option value="Palau">Palau</option>
                                        <option value="U.S. Virgin Islands">U.S. Virgin Islands</option>
                                    </optgroup>
                                    <optgroup label="Rest of world">
                                        <option value="Puerto Rico">Puerto Rico</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Åland Islands">Åland Islands</option>
                                        <option value="Albania">Albania</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="Andorra">Andorra</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Anguilla">Anguilla</option>
                                        <option value="Antarctica">Antarctica</option>
                                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Aruba">Aruba</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Austria">Austria</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahamas">Bahamas</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Barbados">Barbados</option>
                                        <option value="Belarus">Belarus</option>
                                        <option value="Belgium">Belgium</option>
                                        <option value="Belize">Belize</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Bermuda">Bermuda</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Bolivia">Bolivia</option>
                                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Bouvet Island">Bouvet Island</option>
                                        <option value="Brazil">Brazil</option>
                                        <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                        <option value="Brunei Darussalam">Brunei Darussalam</option>
                                        <option value="Bulgaria">Bulgaria</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Cayman Islands">Cayman Islands</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Chile">Chile</option>
                                        <option value="China">China</option>
                                        <option value="Christmas Island">Christmas Island</option>
                                        <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                        <option value="Colombia">Colombia</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                        <option value="Cook Islands">Cook Islands</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Cote D'ivoire">Cote D'ivoire</option>
                                        <option value="Croatia">Croatia</option>
                                        <option value="Cuba">Cuba</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="Czech Republic">Czech Republic</option>
                                        <option value="Denmark">Denmark</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Dominica">Dominica</option>
                                        <option value="Dominican Republic">Dominican Republic</option>
                                        <option value="Ecuador">Ecuador</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="El Salvador">El Salvador</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Estonia">Estonia</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                        <option value="Faroe Islands">Faroe Islands</option>
                                        <option value="Fiji">Fiji</option>
                                        <option value="Finland">Finland</option>
                                        <option value="France">France</option>
                                        <option value="French Guiana">French Guiana</option>
                                        <option value="French Polynesia">French Polynesia</option>
                                        <option value="French Southern Territories">French Southern Territories</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Gibraltar">Gibraltar</option>
                                        <option value="Greece">Greece</option>
                                        <option value="Greenland">Greenland</option>
                                        <option value="Grenada">Grenada</option>
                                        <option value="Guadeloupe">Guadeloupe</option>
                                        <option value="Guatemala">Guatemala</option>
                                        <option value="Guernsey">Guernsey</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-bissau">Guinea-bissau</option>
                                        <option value="Guyana">Guyana</option>
                                        <option value="Haiti">Haiti</option>
                                        <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                        <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                        <option value="Honduras">Honduras</option>
                                        <option value="Hong Kong">Hong Kong</option>
                                        <option value="Hungary">Hungary</option>
                                        <option value="Iceland">Iceland</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Ireland">Ireland</option>
                                        <option value="Isle of Man">Isle of Man</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Jamaica">Jamaica</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jersey">Jersey</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Kiribati">Kiribati</option>
                                        <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                        <option value="Korea, Republic of">Korea, Republic of</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                        <option value="Latvia">Latvia</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                        <option value="Liechtenstein">Liechtenstein</option>
                                        <option value="Lithuania">Lithuania</option>
                                        <option value="Luxembourg">Luxembourg</option>
                                        <option value="Macao">Macao</option>
                                        <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Malta">Malta</option>
                                        <option value="Martinique">Martinique</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Mayotte">Mayotte</option>
                                        <option value="Mexico">Mexico</option>
                                        <option value="Moldova, Republic of">Moldova, Republic of</option>
                                        <option value="Monaco">Monaco</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Montenegro">Montenegro</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Nauru">Nauru</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="Netherlands">Netherlands</option>
                                        <option value="Netherlands Antilles">Netherlands Antilles</option>
                                        <option value="New Caledonia">New Caledonia</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="Nicaragua">Nicaragua</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Niue">Niue</option>
                                        <option value="Norfolk Island">Norfolk Island</option>
                                        <option value="Norway">Norway</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                        <option value="Panama">Panama</option>
                                        <option value="Papua New Guinea">Papua New Guinea</option>
                                        <option value="Paraguay">Paraguay</option>
                                        <option value="Peru">Peru</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Pitcairn">Pitcairn</option>
                                        <option value="Poland">Poland</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Reunion">Reunion</option>
                                        <option value="Romania">Romania</option>
                                        <option value="Russian Federation">Russian Federation</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Saint Helena">Saint Helena</option>
                                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                        <option value="Saint Lucia">Saint Lucia</option>
                                        <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                        <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                        <option value="Samoa">Samoa</option>
                                        <option value="San Marino">San Marino</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Serbia">Serbia</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra Leone">Sierra Leone</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Slovakia">Slovakia</option>
                                        <option value="Slovenia">Slovenia</option>
                                        <option value="Solomon Islands">Solomon Islands</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                        <option value="Spain">Spain</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Suriname">Suriname</option>
                                        <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Sweden">Sweden</option>
                                        <option value="Switzerland">Switzerland</option>
                                        <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                        <option value="Taiwan">Taiwan</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Timor-leste">Timor-leste</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tokelau">Tokelau</option>
                                        <option value="Tonga">Tonga</option>
                                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                        <option value="Tuvalu">Tuvalu</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="United Kingdom">United Kingdom</option>

                                        <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                        <option value="Uruguay">Uruguay</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vanuatu">Vanuatu</option>
                                        <option value="Venezuela">Venezuela</option>
                                        <option value="Viet Nam">Viet Nam</option>
                                        <option value="Virgin Islands, British">Virgin Islands, British</option>
                                        <option value="Wallis and Futuna">Wallis and Futuna</option>
                                        <option value="Western Sahara">Western Sahara</option>
                                        <option value="Yemen">Yemen</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">State*</div>
                                <select name="owner_state" id="owner_state">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="AL">Alabama</option>
                                        <option value="AK">Alaska</option>
                                        <option value="AZ">Arizona</option>
                                        <option value="AR">Arkansas</option>
                                        <option value="CA">California</option>
                                        <option value="CO">Colorado</option>
                                        <option value="CT">Connecticut</option>
                                        <option value="DE">Delaware</option>
                                        <option value="DC">District Of Columbia</option>
                                        <option value="FL">Florida</option>
                                        <option value="GA">Georgia</option>
                                        <option value="HI">Hawaii</option>
                                        <option value="ID">Idaho</option>
                                        <option value="IL">Illinois</option>
                                        <option value="IN">Indiana</option>
                                        <option value="IA">Iowa</option>
                                        <option value="KS">Kansas</option>
                                        <option value="KY">Kentucky</option>
                                        <option value="LA">Louisiana</option>
                                        <option value="ME">Maine</option>
                                        <option value="MD">Maryland</option>
                                        <option value="MA">Massachusetts</option>
                                        <option value="MI">Michigan</option>
                                        <option value="MN">Minnesota</option>
                                        <option value="MS">Mississippi</option>
                                        <option value="MO">Missouri</option>
                                        <option value="MT">Montana</option>
                                        <option value="NE">Nebraska</option>
                                        <option value="NV">Nevada</option>
                                        <option value="NH">New Hampshire</option>
                                        <option value="NJ">New Jersey</option>
                                        <option value="NM">New Mexico</option>
                                        <option value="NY">New York</option>
                                        <option value="NC">North Carolina</option>
                                        <option value="ND">North Dakota</option>
                                        <option value="OH">Ohio</option>
                                        <option value="OK">Oklahoma</option>
                                        <option value="OR">Oregon</option>
                                        <option value="PA">Pennsylvania</option>
                                        <option value="RI">Rhode Island</option>
                                        <option value="SC">South Carolina</option>
                                        <option value="SD">South Dakota</option>
                                        <option value="TN">Tennessee</option>
                                        <option value="TX">Texas</option>
                                        <option value="UT">Utah</option>
                                        <option value="VT">Vermont</option>
                                        <option value="VA">Virginia</option>
                                        <option value="WA">Washington</option>
                                        <option value="WV">West Virginia</option>
                                        <option value="WI">Wisconsin</option>
                                        <option value="WY">Wyoming</option>
                                    </optgroup>

                                    <optgroup label="US Outlying Territories">
                                        <option value="AS">American Samoa</option>
                                        <option value="GU">Guam</option>
                                        <option value="MP">Northern Mariana Islands</option>
                                        <option value="PR">Puerto Rico</option>
                                        <option value="UM">United States Minor Outlying Islands</option>
                                        <option value="VI">Virgin Islands</option>
                                    </optgroup>

                                    <optgroup label="US Armed Forces">
                                        <option value="AA">Armed Forces Americas</option>
                                        <option value="AP">Armed Forces Pacific</option>
                                        <option value="AE">Armed Forces Others</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="field col1-3">
                                <div class="field-title">Zip Code*</div>
                                <input type="text" name="owner_zip" id="owner_zip" placeholder="Write Zip Code">
                            </div>
                        </div>
                        <div class="tab-fields-header">
                            <div class="sub-fields-title">Identity Verification</div>
                            <div class="sub-fields-description">Identity information of the beneficial owner.</div>
                        </div>
                        <div class="tab-fields">
                            <div class="field">
                                <div class="field-title">Upload image of ID (.png, .jpg, .pdf)*</div>
                                <input type="file" name="owner_id_image" id="owner_id_image" placeholder="Add ID Image" accept="image/png, image/jpeg, image/jpg, application/pdf">
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">What is the ID Type?*</div>
                                <select name="owner_id_type" id="owner_id_type">
                                    <option value="">Select an option</option>
                                    <option value="state_drivers_license">State Issued Driver's License</option>
                                    <option value="state_local_id">State/local/tribal-issued ID</option>
                                    <option value="us_passport">U.S. Passport</option>
                                    <option value="foreign_passport">Foreign Passport</option>
                                </select>
                            </div>
                            <div class="field col1-2">
                                <div class="field-title">ID Number*</div>
                                <input type="text" name="owner_id_number" id="owner_id_number" placeholder="Write ID Number" maxlength="18">
                            </div>
                            <div class="field">
                                <div class="field-title">Country*</div>
                                <select name="owner_id_country" id="owner_id_country">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="United States">United States of America</option>
                                        <option value="American Samoa">American Samoa</option>
                                        <option value="Guam">Guam</option>
                                        <option value="Marshal Islands">Marshal Islands</option>
                                        <option value="Micronesia, Federated States">Micronesia, Federated States</option>
                                        <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                        <option value="Palau">Palau</option>
                                        <option value="U.S. Virgin Islands">U.S. Virgin Islands</option>
                                    </optgroup>
                                    <optgroup label="Rest of world">
                                        <option value="Puerto Rico">Puerto Rico</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Åland Islands">Åland Islands</option>
                                        <option value="Albania">Albania</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="Andorra">Andorra</option>
                                        <option value="Angola">Angola</option>
                                        <option value="Anguilla">Anguilla</option>
                                        <option value="Antarctica">Antarctica</option>
                                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Armenia">Armenia</option>
                                        <option value="Aruba">Aruba</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Austria">Austria</option>
                                        <option value="Azerbaijan">Azerbaijan</option>
                                        <option value="Bahamas">Bahamas</option>
                                        <option value="Bahrain">Bahrain</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Barbados">Barbados</option>
                                        <option value="Belarus">Belarus</option>
                                        <option value="Belgium">Belgium</option>
                                        <option value="Belize">Belize</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Bermuda">Bermuda</option>
                                        <option value="Bhutan">Bhutan</option>
                                        <option value="Bolivia">Bolivia</option>
                                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                        <option value="Botswana">Botswana</option>
                                        <option value="Bouvet Island">Bouvet Island</option>
                                        <option value="Brazil">Brazil</option>
                                        <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                        <option value="Brunei Darussalam">Brunei Darussalam</option>
                                        <option value="Bulgaria">Bulgaria</option>
                                        <option value="Burkina Faso">Burkina Faso</option>
                                        <option value="Burundi">Burundi</option>
                                        <option value="Cambodia">Cambodia</option>
                                        <option value="Cameroon">Cameroon</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Cape Verde">Cape Verde</option>
                                        <option value="Cayman Islands">Cayman Islands</option>
                                        <option value="Central African Republic">Central African Republic</option>
                                        <option value="Chad">Chad</option>
                                        <option value="Chile">Chile</option>
                                        <option value="China">China</option>
                                        <option value="Christmas Island">Christmas Island</option>
                                        <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                        <option value="Colombia">Colombia</option>
                                        <option value="Comoros">Comoros</option>
                                        <option value="Congo">Congo</option>
                                        <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                        <option value="Cook Islands">Cook Islands</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Cote D'ivoire">Cote D'ivoire</option>
                                        <option value="Croatia">Croatia</option>
                                        <option value="Cuba">Cuba</option>
                                        <option value="Cyprus">Cyprus</option>
                                        <option value="Czech Republic">Czech Republic</option>
                                        <option value="Denmark">Denmark</option>
                                        <option value="Djibouti">Djibouti</option>
                                        <option value="Dominica">Dominica</option>
                                        <option value="Dominican Republic">Dominican Republic</option>
                                        <option value="Ecuador">Ecuador</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="El Salvador">El Salvador</option>
                                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                                        <option value="Eritrea">Eritrea</option>
                                        <option value="Estonia">Estonia</option>
                                        <option value="Ethiopia">Ethiopia</option>
                                        <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                        <option value="Faroe Islands">Faroe Islands</option>
                                        <option value="Fiji">Fiji</option>
                                        <option value="Finland">Finland</option>
                                        <option value="France">France</option>
                                        <option value="French Guiana">French Guiana</option>
                                        <option value="French Polynesia">French Polynesia</option>
                                        <option value="French Southern Territories">French Southern Territories</option>
                                        <option value="Gabon">Gabon</option>
                                        <option value="Gambia">Gambia</option>
                                        <option value="Georgia">Georgia</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Gibraltar">Gibraltar</option>
                                        <option value="Greece">Greece</option>
                                        <option value="Greenland">Greenland</option>
                                        <option value="Grenada">Grenada</option>
                                        <option value="Guadeloupe">Guadeloupe</option>
                                        <option value="Guatemala">Guatemala</option>
                                        <option value="Guernsey">Guernsey</option>
                                        <option value="Guinea">Guinea</option>
                                        <option value="Guinea-bissau">Guinea-bissau</option>
                                        <option value="Guyana">Guyana</option>
                                        <option value="Haiti">Haiti</option>
                                        <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                        <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                        <option value="Honduras">Honduras</option>
                                        <option value="Hong Kong">Hong Kong</option>
                                        <option value="Hungary">Hungary</option>
                                        <option value="Iceland">Iceland</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Ireland">Ireland</option>
                                        <option value="Isle of Man">Isle of Man</option>
                                        <option value="Israel">Israel</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Jamaica">Jamaica</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jersey">Jersey</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kazakhstan">Kazakhstan</option>
                                        <option value="Kenya">Kenya</option>
                                        <option value="Kiribati">Kiribati</option>
                                        <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                        <option value="Korea, Republic of">Korea, Republic of</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                                        <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                        <option value="Latvia">Latvia</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Lesotho">Lesotho</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                        <option value="Liechtenstein">Liechtenstein</option>
                                        <option value="Lithuania">Lithuania</option>
                                        <option value="Luxembourg">Luxembourg</option>
                                        <option value="Macao">Macao</option>
                                        <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                        <option value="Madagascar">Madagascar</option>
                                        <option value="Malawi">Malawi</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Maldives">Maldives</option>
                                        <option value="Mali">Mali</option>
                                        <option value="Malta">Malta</option>
                                        <option value="Martinique">Martinique</option>
                                        <option value="Mauritania">Mauritania</option>
                                        <option value="Mauritius">Mauritius</option>
                                        <option value="Mayotte">Mayotte</option>
                                        <option value="Mexico">Mexico</option>
                                        <option value="Moldova, Republic of">Moldova, Republic of</option>
                                        <option value="Monaco">Monaco</option>
                                        <option value="Mongolia">Mongolia</option>
                                        <option value="Montenegro">Montenegro</option>
                                        <option value="Montserrat">Montserrat</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Mozambique">Mozambique</option>
                                        <option value="Myanmar">Myanmar</option>
                                        <option value="Namibia">Namibia</option>
                                        <option value="Nauru">Nauru</option>
                                        <option value="Nepal">Nepal</option>
                                        <option value="Netherlands">Netherlands</option>
                                        <option value="Netherlands Antilles">Netherlands Antilles</option>
                                        <option value="New Caledonia">New Caledonia</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="Nicaragua">Nicaragua</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Niue">Niue</option>
                                        <option value="Norfolk Island">Norfolk Island</option>
                                        <option value="Norway">Norway</option>
                                        <option value="Oman">Oman</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                        <option value="Panama">Panama</option>
                                        <option value="Papua New Guinea">Papua New Guinea</option>
                                        <option value="Paraguay">Paraguay</option>
                                        <option value="Peru">Peru</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Pitcairn">Pitcairn</option>
                                        <option value="Poland">Poland</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Reunion">Reunion</option>
                                        <option value="Romania">Romania</option>
                                        <option value="Russian Federation">Russian Federation</option>
                                        <option value="Rwanda">Rwanda</option>
                                        <option value="Saint Helena">Saint Helena</option>
                                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                        <option value="Saint Lucia">Saint Lucia</option>
                                        <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                        <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                        <option value="Samoa">Samoa</option>
                                        <option value="San Marino">San Marino</option>
                                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Senegal">Senegal</option>
                                        <option value="Serbia">Serbia</option>
                                        <option value="Seychelles">Seychelles</option>
                                        <option value="Sierra Leone">Sierra Leone</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Slovakia">Slovakia</option>
                                        <option value="Slovenia">Slovenia</option>
                                        <option value="Solomon Islands">Solomon Islands</option>
                                        <option value="Somalia">Somalia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                        <option value="Spain">Spain</option>
                                        <option value="Sri Lanka">Sri Lanka</option>
                                        <option value="Sudan">Sudan</option>
                                        <option value="Suriname">Suriname</option>
                                        <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                        <option value="Swaziland">Swaziland</option>
                                        <option value="Sweden">Sweden</option>
                                        <option value="Switzerland">Switzerland</option>
                                        <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                        <option value="Taiwan">Taiwan</option>
                                        <option value="Tajikistan">Tajikistan</option>
                                        <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Timor-leste">Timor-leste</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Tokelau">Tokelau</option>
                                        <option value="Tonga">Tonga</option>
                                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                        <option value="Tunisia">Tunisia</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="Turkmenistan">Turkmenistan</option>
                                        <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                        <option value="Tuvalu">Tuvalu</option>
                                        <option value="Uganda">Uganda</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                        <option value="United Kingdom">United Kingdom</option>

                                        <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                        <option value="Uruguay">Uruguay</option>
                                        <option value="Uzbekistan">Uzbekistan</option>
                                        <option value="Vanuatu">Vanuatu</option>
                                        <option value="Venezuela">Venezuela</option>
                                        <option value="Viet Nam">Viet Nam</option>
                                        <option value="Virgin Islands, British">Virgin Islands, British</option>
                                        <option value="Wallis and Futuna">Wallis and Futuna</option>
                                        <option value="Western Sahara">Western Sahara</option>
                                        <option value="Yemen">Yemen</option>
                                        <option value="Zambia">Zambia</option>
                                        <option value="Zimbabwe">Zimbabwe</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="tab-fields has-or-divider-group3">
                            <div class="field col1-2 has-or-divider">
                                <div class="field-title">State</div>
                                <select name="owner_id_state" id="owner_id_state">
                                    <option value="">Please select a value</option>
                                    <optgroup label="United States">
                                        <option value="AL">Alabama</option>
                                        <option value="AK">Alaska</option>
                                        <option value="AZ">Arizona</option>
                                        <option value="AR">Arkansas</option>
                                        <option value="CA">California</option>
                                        <option value="CO">Colorado</option>
                                        <option value="CT">Connecticut</option>
                                        <option value="DE">Delaware</option>
                                        <option value="DC">District Of Columbia</option>
                                        <option value="FL">Florida</option>
                                        <option value="GA">Georgia</option>
                                        <option value="HI">Hawaii</option>
                                        <option value="ID">Idaho</option>
                                        <option value="IL">Illinois</option>
                                        <option value="IN">Indiana</option>
                                        <option value="IA">Iowa</option>
                                        <option value="KS">Kansas</option>
                                        <option value="KY">Kentucky</option>
                                        <option value="LA">Louisiana</option>
                                        <option value="ME">Maine</option>
                                        <option value="MD">Maryland</option>
                                        <option value="MA">Massachusetts</option>
                                        <option value="MI">Michigan</option>
                                        <option value="MN">Minnesota</option>
                                        <option value="MS">Mississippi</option>
                                        <option value="MO">Missouri</option>
                                        <option value="MT">Montana</option>
                                        <option value="NE">Nebraska</option>
                                        <option value="NV">Nevada</option>
                                        <option value="NH">New Hampshire</option>
                                        <option value="NJ">New Jersey</option>
                                        <option value="NM">New Mexico</option>
                                        <option value="NY">New York</option>
                                        <option value="NC">North Carolina</option>
                                        <option value="ND">North Dakota</option>
                                        <option value="OH">Ohio</option>
                                        <option value="OK">Oklahoma</option>
                                        <option value="OR">Oregon</option>
                                        <option value="PA">Pennsylvania</option>
                                        <option value="RI">Rhode Island</option>
                                        <option value="SC">South Carolina</option>
                                        <option value="SD">South Dakota</option>
                                        <option value="TN">Tennessee</option>
                                        <option value="TX">Texas</option>
                                        <option value="UT">Utah</option>
                                        <option value="VT">Vermont</option>
                                        <option value="VA">Virginia</option>
                                        <option value="WA">Washington</option>
                                        <option value="WV">West Virginia</option>
                                        <option value="WI">Wisconsin</option>
                                        <option value="WY">Wyoming</option>
                                    </optgroup>

                                    <optgroup label="US Outlying Territories">
                                        <option value="AS">American Samoa</option>
                                        <option value="GU">Guam</option>
                                        <option value="MP">Northern Mariana Islands</option>
                                        <option value="PR">Puerto Rico</option>
                                        <option value="UM">United States Minor Outlying Islands</option>
                                        <option value="VI">Virgin Islands</option>
                                    </optgroup>

                                    <optgroup label="US Armed Forces">
                                        <option value="AA">Armed Forces Americas</option>
                                        <option value="AP">Armed Forces Pacific</option>
                                        <option value="AE">Armed Forces Others</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="field or-divider">
                                OR
                            </div>
                            <div class="field col1-2 has-or-divider">
                                <div class="field-title">Tribal Jurisdiction of Registration</div>
                                <input type="text" name="owner_id_tribal_jurisdiction" id="owner_id_tribal_jurisdiction" placeholder="Write Tribal Jurisdiction of Registration">
                            </div>
                        </div>
                        <div class="tab-fields">
                            <div class="form-buttons">
                                <button class="button-primary" id="boir_form4x_submit"><i class="bi bi-plus-lg"></i> Add Owner</button>
                                <span class="loader hidden" ></span>
                                <div class="error-message hidden" id='add_owner_error'></div>
                            </div>
                        </div>
                    </div>
                    <div class="data-card beneficial_owners_form" data-id="2" data-found="<?=$fillings_beneficial_owners_status;?>">

                    </div>
                    <div class="tab-fields">
															                    <div class="form-buttons">
                        <button class="button-primary" id="back_btn" data-payment-type="4">Back</button>
                    </div>
                        <div class="form-buttons">
                            <button class="button-primary" id="boir_form4_submit">Continue to Review <i class="bi bi-arrow-right"></i></button>
                            <span class="loader hidden" ></span>
                            <div class="error-message hidden"></div>
                        </div>
                    </div>
                </div>
                <div class="step <?=$step>=4?'active':''?> <?=$step>4?'completed':''?>" data-step="4" id="BOIRForm5">
                    <div class="tab-fields-header">
                        <div class="fields-title">Review Your BOIR Filing</div>
                        <div class="fields-description">Review and submit your BOIR Filing.</div>
                    </div>
                    <div class="tab-data-review-list">
                        <div class="tab-data-review-item">
                            <div class="item-title">Initial Information</div>
                            <div class="item-content">
                                <i class="bi bi-check-circle"></i>
                                <span class="item-badge">Completed</span>
                                <button class="button-primary" onclick="moveTabs(1);">Edit</button>
                            </div>
                        </div>
                        <div class="tab-data-review-item">
                            <div class="item-title">Company Details</div>
                            <div class="item-content">
                                <i class="bi bi-check-circle"></i>
                                <span class="item-badge">Completed</span>
                                <button class="button-primary" onclick="moveSteps(1);">Edit</button>
                            </div>
                        </div>
                        <div class="tab-data-review-item">
                            <div class="item-title">Company Applicant(s)</div>
                            <div class="item-content">
                                <i class="bi bi-check-circle"></i>
                                <span class="item-badge">Completed</span>
                                <button class="button-primary" onclick="moveSteps(2);">Edit</button>
                            </div>
                        </div>
                        <div class="tab-data-review-item">
                            <div class="item-title">Beneficial Owner(s)</div>
                            <div class="item-content">
                                <i class="bi bi-check-circle"></i>
                                <span class="item-badge">Completed</span>
                                <button class="button-primary" onclick="moveSteps(3);">Edit</button>
                            </div>
                        </div>

                    </div>
                    <div class="special-box">
                        <label class="box-checkbox" for="filling_authorization">
                            <input class="checkbox" type="checkbox" name="filling_authorization" id="filling_authorization" value="1" <?=$filling_authorization==1?'checked':''?>>
                            <label  for="filling_authorization" class="no-padding">
                                <p>I certify that I am authorized to file this BOIR on behalf of the reporting company. I further certify, on behalf of the reporting company, that the information contained in this BOIR is true, correct, and complete.</p>
                            </label>
                        </label>
                    </div>
                    <div class="tab-fields">

                        <div class="form-buttons">
                            <button class="button-primary" id="boir_form5_submit"><?php if($fillings_payments_status==true){ echo 'Update Filling'; } else { echo 'Checkout'; } ?> <i class="bi bi-arrow-right"></i></button>
                            <span class="loader hidden" ></span>
                            <div class="error-message hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab <?=$tab==3?'active':''?> <?=$tab>3?'completed':''?> tab-3" id="BOIRForm6">
            <input type="hidden" id="boir_form_6_checkout_processor" value="<?php echo ($checkout_processor == 'gpay') ? 'nmi' : $checkout_processor; ?>">
            <div class="tab-fields-header payment-detail <?php echo ($checkout_processor != 'ach') ? 'hidden' : ''; ?>" style="padding-bottom: 0px;">
                <div class="fields-title">Payment Detail</div>
                <div style="margin-top: 1rem;">
                    Please Provide the payment details for this filling
                </div>
                <img class="d-block ach-payment-img" src="/wp-content/uploads/2024/12/image_2024_12_07T13_47_17_266Z.png" style="width: 50%;">
            </div>
            <div class="tab-fields-header hidden <?php echo ($checkout_processor == 'gpay' || $checkout_processor == 'airwallex') ? 'hidden' : ''; ?>">
                <div class="fields-title">Account Type</div>
                <div class="payment-tabs-btns">
                    <button class="payment-tab-btn payment_type <?php echo ($checkout_processor == 'nmi') ? 'active' : ''; ?>" id="payment_type_1" data-type="1">Credit Or Dabit Card</button>
                    <button class="payment-tab-btn payment_type" id="payment_type_2" data-type="2">Personal Checking</button>
                    <button class="payment-tab-btn payment_type" id="payment_type_3" data-type="3">Personal Saving</button>
                    <button class="payment-tab-btn payment_type <?php echo ($checkout_processor == 'ach') ? 'active' : ''; ?>" id="payment_type_4" data-type="4">Business Checking</button>
                    <button class="payment-tab-btn payment_type" id="payment_type_5" data-type="5">Business Saving</button>
                </div>
            </div>
            <div class="gpayment-tab-fields-header <?php echo ($checkout_processor != 'gpay') ? 'hidden' : ''; ?>">
                <div class="fields-title">Payment Method</div>
                <div class="payment-tabs-btns">
                    <button class="gpayment-tab-btn payment_type active" id="payment_type_1" data-type="3">
                        <img class="d-block checkout-method-icons-selected" src="/wp-content/uploads/2024/12/card-logos.png" width="125px" height="45px" style="margin:auto !important">
                        <div>Credit Or Dabit Card</div>
                    </button>
                    <button class="gpayment-tab-btn payment_type" id="payment_type_1" data-type="1">
                        <img class="d-block checkout-method-icons-selected" src="/wp-content/uploads/2024/12/GooglePay.png" style="margin:auto !important; height: 39px;">
                        <div>Google Pay</div>
                    </button>
                    <button class="gpayment-tab-btn payment_type" id="payment_type_2" data-type="2">
                        <img class="d-block checkout-method-icons-selected" src="/wp-content/uploads/2024/12/ApplePay.png" style="margin:auto !important; height: 39px">
                        <div>Apple Pay</div>
                    </button>
                </div>
            </div>
            <hr class="tab-divider" />
            <div class="payment-tabs">
                <div class="payment-tab <?php echo ($checkout_processor == 'nmi') ? 'active' : ''; ?>" data-type="1">
                    <div class="tab-fields">
                        <div class="field">
                            <div class="field-title">Cardholder Name</div>
                            <input type="text" name="payment_card_holder" id="payment_card_holder" placeholder="Cardholder Name" style="font-family: monospace">
                        </div>
                        <div class="field col2-4">
                            <div class="field-title">Card Number</div>
                            <input type="text" name="payment_card_number" id="payment_card_number" placeholder="1234-1234-1234-1234" style="font-family: monospace">
                        </div>
                        <div class="field col1-4">
                            <div class="field-title">Expiration Date</div>
                            <input type="text" name="payment_exp_date" id="payment_exp_date" placeholder="MM/YY">
                        </div>
                        <div class="field col1-4">
                            <div class="field-title">CVV</div>
                            <input type="text" name="payment_cvv" id="payment_cvv" placeholder="CVV">
                        </div>
                        <div class="field col1-2">
                            <div class="field-title">Country*</div>
                            <select name="payment_card_country" id="payment_card_country">
                                <option value="">Please select a value</option>
                                <optgroup label="United States">
                                    <option value="United States">United States of America</option>
                                    <option value="American Samoa">American Samoa</option>
                                    <option value="Guam">Guam</option>
                                    <option value="Marshal Islands">Marshal Islands</option>
                                    <option value="Micronesia, Federated States">Micronesia, Federated States</option>
                                    <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                    <option value="Palau">Palau</option>
                                    <option value="U.S. Virgin Islands">U.S. Virgin Islands</option>
                                </optgroup>
                                <optgroup label="Rest of world">
                                    <option value="Puerto Rico">Puerto Rico</option>
                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Åland Islands">Åland Islands</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Anguilla">Anguilla</option>
                                    <option value="Antarctica">Antarctica</option>
                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Aruba">Aruba</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bermuda">Bermuda</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Bouvet Island">Bouvet Island</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                    <option value="Brunei Darussalam">Brunei Darussalam</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Cape Verde">Cape Verde</option>
                                    <option value="Cayman Islands">Cayman Islands</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Christmas Island">Christmas Island</option>
                                    <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo">Congo</option>
                                    <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                    <option value="Cook Islands">Cook Islands</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Cote D'ivoire">Cote D'ivoire</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czech Republic">Czech Republic</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                    <option value="Eritrea">Eritrea</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                    <option value="Faroe Islands">Faroe Islands</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="French Guiana">French Guiana</option>
                                    <option value="French Polynesia">French Polynesia</option>
                                    <option value="French Southern Territories">French Southern Territories</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Gibraltar">Gibraltar</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Greenland">Greenland</option>
                                    <option value="Grenada">Grenada</option>
                                    <option value="Guadeloupe">Guadeloupe</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guernsey">Guernsey</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guinea-bissau">Guinea-bissau</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                    <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hong Kong">Hong Kong</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Isle of Man">Isle of Man</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jersey">Jersey</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kiribati">Kiribati</option>
                                    <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                    <option value="Korea, Republic of">Korea, Republic of</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                    <option value="Liechtenstein">Liechtenstein</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Macao">Macao</option>
                                    <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Martinique">Martinique</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mayotte">Mayotte</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Moldova, Republic of">Moldova, Republic of</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montenegro">Montenegro</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Nauru">Nauru</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="Netherlands Antilles">Netherlands Antilles</option>
                                    <option value="New Caledonia">New Caledonia</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Niue">Niue</option>
                                    <option value="Norfolk Island">Norfolk Island</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Pitcairn">Pitcairn</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Reunion">Reunion</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russian Federation">Russian Federation</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Saint Helena">Saint Helena</option>
                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                    <option value="Saint Lucia">Saint Lucia</option>
                                    <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                    <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                    <option value="Samoa">Samoa</option>
                                    <option value="San Marino">San Marino</option>
                                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Solomon Islands">Solomon Islands</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                    <option value="Swaziland">Swaziland</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Timor-leste">Timor-leste</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Tokelau">Tokelau</option>
                                    <option value="Tonga">Tonga</option>
                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                    <option value="Tuvalu">Tuvalu</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>

                                    <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Vanuatu">Vanuatu</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Viet Nam">Viet Nam</option>
                                    <option value="Virgin Islands, British">Virgin Islands, British</option>
                                    <option value="Wallis and Futuna">Wallis and Futuna</option>
                                    <option value="Western Sahara">Western Sahara</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="field col1-2">
                            <div class="field-title">Zip Code*</div>
                            <input type="text" name="payment_card_zip" id="payment_card_zip" placeholder="Write your zip code">
                        </div>
                    </div>
                </div>
                <div class="payment-tab <?php echo ($checkout_processor == 'ach') ? 'active' : ''; ?>" data-type="2">
                    <div class="tab-fields">
                        <div class="field">
                            <div class="field-title">Routing Number*</div>
                            <input type="text" name="routing_number" id="routing_number" placeholder="Write your routing number">
                        </div>
                        <div class="field col1-2">
                            <div class="field-title">Account Number*</div>
                            <input type="text" name="account_number" id="account_number" placeholder="Write your account number">
                        </div>
                        <div class="field col1-2">
                            <div class="field-title">Confirm Account Number*</div>
                            <input type="text" name="confirm_account_number" id="confirm_account_number" placeholder="Confirm your account number">
                        </div>
                    </div>
                    
                </div>
                <div class="payment-tab <?php echo ($checkout_processor == 'braintree') ? 'active' : ''; ?>" data-type="3">
                    <div id="dropin-container-card"></div>
                    <div class="g-recaptcha" data-sitekey="6LcOBYQqAAAAAMQIAqB2EM2FHTj5FHGcwm4q2Bvk"></div>
                    <span class="g-recaptcha-error"></span>
                </div>
                <div class="payment-tab <?php echo ($checkout_processor == 'gpay') ? 'active' : ''; ?>" data-type="4">
                    <div class="dropin-container" id="dropin-container-google" data-type="1"></div>
                    <div class="dropin-container" id="dropin-container-apple" data-type="2"></div>
                </div>
                <div class="payment-tab <?php echo ($checkout_processor == 'airwallex') ? 'active' : ''; ?>" data-type="5">
                    <div id="dropin-airwallex"></div>
                </div>
                <div class="payment-tab <?php echo ($checkout_processor == 'airwallex1') ? 'active' : ''; ?>" data-type="6">
                    <div class="tab-fields">
                        <div class="field">
                            <div class="field-title">Cardholder Name</div>
                            <input type="text" name="airwallex_card_holder" id="airwallex_card_holder" placeholder="Cardholder Name" style="font-family: monospace">
                        </div>
                        <div class="field col2-4">
                            <div class="field-title">Card Number</div>
                            <input type="text" name="airwallex_card_number" id="airwallex_card_number" placeholder="1234-1234-1234-1234" style="font-family: monospace">
                        </div>
                        <div class="field col1-4">
                            <div class="field-title">Expiration Date</div>
                            <input type="text" name="airwallex_exp_date" id="airwallex_exp_date" placeholder="MM/YY">
                        </div>
                        <div class="field col1-4">
                            <div class="field-title">CVV</div>
                            <input type="text" name="airwallex_cvv" id="airwallex_cvv" placeholder="CVV">
                        </div>
                        <div class="field col1-2">
                            <div class="field-title">Zip Code*</div>
                            <input type="text" name="airwallex_card_zip" id="airwallex_card_zip" placeholder="Write your zip code">
                        </div>
                    </div>
                </div>
                <div class="tab-fields">
                    <div class="form-buttons">
                        <button class="button-primary" id="back_btn" data-payment-type="6">Back</button>
                    </div>
                    <div class="form-buttons button-continue <?php echo ($checkout_processor == 'airwallex') ? 'hidden' : ''; ?>">
                        <button class="button-primary" id="boir_form6_submit" data-payment-type="1">Continue <i class="bi bi-arrow-right"></i></button>
                        <span class="loader hidden" ></span>
                        <div class="error-message hidden"></div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</main>

<?php } ?>
