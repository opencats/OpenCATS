<?php 

function tplView() {
?>	
<mvc:View
	xmlns:u="sap.ui.unified"
   xmlns="sap.m"
   xmlns:mvc="sap.ui.core.mvc">
   <MaskInput name="dupa" mask = "~~~~~~~~~~" placeholderSymbol = "_" placeholder = "Enter text" >
   								<rules>
									<MaskInputRule maskFormatSymbol = "~" regex = "[^_]"/>
								</rules>
							</MaskInput>
	<Label text="Plik:"/>
	<u:FileUploader
			width="400px"
			tooltip="Załaduj plik Excel (*.xls)"
			style="Emphasized"
			name="file"
			placeholder="Wybierz plik do załadowania..."/>   
</mvc:View>
<?php 	
} 

function tplViewxx(){
?>
<mvc:View
		height="100%"
		controllerName="sap.m.sample.Wizard.C"
		xmlns:layout="sap.ui.layout"
		xmlns:form="sap.ui.layout.form"
		xmlns:core="sap.ui.core"
		xmlns:u="sap.ui.unified"
		xmlns:mvc="sap.ui.core.mvc"
		xmlns="sap.m">
		<NavContainer id="wizardNavContainer">
			<pages>
				<Page
				id="wizardContentPage"
				showHeader="false">
					<content>
						<Wizard id="CreateProductWizard"
								complete="wizardCompletedHandler">
							<WizardStep id="ProductTypeStep"
										title="Product Type"
										validated="true">
								<MessageStrip class="sapUiSmallMarginBottom"
											  text="The Wizard control is supposed to break down large tasks, into smaller steps, easier for the user to work with."
											  showIcon="true"/>
								<Text class="sapUiSmallMarginBottom"
									  text="Sed fermentum, mi et tristique ullamcorper, sapien sapien faucibus sem, quis pretium nibh lorem malesuada diam. Nulla quis arcu aliquet, feugiat massa semper, volutpat diam. Nam vitae ante posuere, molestie neque sit amet, dapibus velit. Maecenas eleifend tempor lorem. Mauris vitae elementum mi, sed eleifend ligula. Nulla tempor vulputate dolor, nec dignissim quam convallis ut. Praesent vitae commodo felis, ut iaculis felis. Fusce quis eleifend sapien, eget facilisis nibh. Suspendisse est velit, scelerisque ut commodo eget, dignissim quis metus. Cras faucibus consequat gravida. Curabitur vitae quam felis. Phasellus ac leo eleifend, commodo tortor et, varius quam. Aliquam erat volutpat"/>
								<HBox
										alignItems="Center"
										justifyContent="Center"
										width="100%">
									<SegmentedButton
											width="320px"
											select="setProductTypeFromSegmented">
										<Button icon="sap-icon://iphone" text="Mobile"/>
										<Button icon="sap-icon://sys-monitor" text="Desktop"/>
										<Button icon="sap-icon://database" text="Other"/>
									</SegmentedButton>
								</HBox>
							</WizardStep>
							<WizardStep id="ProductInfoStep"
										validated="false"
										title="Product Information"
										activate="additionalInfoValidation">
								<MessageStrip class="sapUiSmallMarginBottom"
											  text="Validation in the wizard is controlled by calling the validateStep(Step) and invalidateStep(Step) methods "
											  showIcon="true"/>
								<Text text="Cras tellus leo, volutpat vitae ullamcorper eu, posuere malesuada nisl. Integer pellentesque leo sit amet dui vehicula, quis ullamcorper est pulvinar. Nam in libero sem. Suspendisse arcu metus, molestie a turpis a, molestie aliquet dui. Donec pulvinar, sapien et viverra imperdiet, orci erat porttitor nulla, eget commodo metus nibh nec ipsum. Aliquam lacinia euismod metus, sollicitudin pellentesque purus volutpat eget. Pellentesque egestas erat quis eros convallis mattis. Mauris hendrerit sapien a malesu corper eu, posuere malesuada nisl. Integer pellentesque leo sit amet dui vehicula, quis ullamcorper est pulvinar. Nam in libero sem. Suspendisse arcu metus, molestie a turpis a, molestie aliquet dui. Donec pulvinar, sapien  corper eu, posuere malesuada nisl. Integer pellentesque leo sit amet dui vehicula, quis ullamcorper est pulvinar. Nam in libero sem. Suspendisse arcu metus, molestie a turpis a, molestie aliquet dui. Donec pulvinar, sapien  "/>
								<form:SimpleForm
										editable="true">
									<Label text="Name" required="true"/>
									<Input valueStateText="Enter 6 symbols or more"
										   valueState="{/productNameState}" id="ProductName" liveChange="additionalInfoValidation"
										   placeholder="Enter name with length greater than 6" value="{/productName}"/>
									<Label text="Weight" required="true"/>
									<Input valueStateText="Enter digits"
										   valueState="{/productWeightState}" id="ProductWeight"
										   liveChange="additionalInfoValidation" type="Number" placeholder="Enter digits"
										   value="{/productWeight}"/>
									<Label text="Manufacturer"/>
									<Select selectedKey="{/productManufacturer}" width="200px">
										<core:Item key="Apple" text="Apple"/>
										<core:Item key="Microsoft" text="Microsoft"/>
										<core:Item key="Google" text="Google"/>
										<core:Item key="Sony" text="Sony"/>
										<core:Item key="Samsung" text="Samsung"/>
										<core:Item key="Logitech" text="Logitech"/>
									</Select>
									<Label text="Description"/>
									<TextArea value="{/productDescription}" rows="8"/>
								</form:SimpleForm>
							</WizardStep>
							<WizardStep id="OptionalInfoStep"
										validated="true"
										activate="optionalStepActivation"
										title="Optional Information">
								<MessageStrip class="sapUiSmallMarginBottom"
											  text="You can validate steps by default with the validated='true' property of the step. The next button is always enabled."
											  showIcon="true"/>
								<Text text="Integer pellentesque leo sit amet dui vehicula, quis ullamcorper est pulvinar. Nam in libero sem. Suspendisse arcu metus, molestie a turpis a, molestie aliquet dui. Donec ppellentesque leo sit amet dui vehicula, quis ullamcorper est pulvinar. Nam in libero sem. Suspendisse arcu metus, molestie a turpis a, molestie aliquet dui. Donec pulvinar, sapien  corper eu, posuere malesuada nisl. Integer pellentesque leo sit amet dui vehicula, quis ullamcorper est pulvinar. Nam in libero sem. Suspendisse arcu metus, molestie a turpis a, molestie aliquet dui. Donec pulvinar, sapien  "/>
								<form:SimpleForm
										editable="true">
									<Label text="Cover photo"/>
									<u:FileUploader
											width="400px"
											tooltip="Upload product cover photo to the local server"
											style="Emphasized"
											placeholder="Choose a file for Upload..."/>
									<Label text="Manufacturing date"/>
									<DatePicker
											id="DP3"
											displayFormat="short"
											change="handleChange"/>
									<Label text="Availability"/>
									<SegmentedButton selectedButton="inStock">
										<Button text="In store" id="inStock"/>
										<Button text="In depot"/>
										<Button text="In repository"/>
										<Button text="Out of stock"/>
									</SegmentedButton>
									<Label text="Size"/>
									<Input/>
									<ComboBox maxWidth="100px">
										<core:Item key="X" text="X"/>
										<core:Item key="Y" text="Y"/>
										<core:Item key="Z" text="Z"/>
									</ComboBox>
 
								</form:SimpleForm>
							</WizardStep>
							<WizardStep id="PricingStep"
										activate="pricingActivate"
										complete="pricingComplete"
										validated="true"
										title="Pricing">
								<MessageStrip class="sapUiSmallMarginBottom"
											  text="You can use the wizard previousStep() and nextStep() methods to navigate from step to step without validation. Also you can use the GoToStep(step) method to scroll programmatically to previously visited steps."
											  showIcon="true"/>
								<form:SimpleForm
										editable="true">
									<Label text="Price"/>
									<Input value="{/productPrice}"/>
									<Label text="Discoung group"/>
									<MultiComboBox>
										<core:Item key="Children" text="Kids"/>
										<core:Item key="Children" text="Teens"/>
										<core:Item key="Children" text="Adults"/>
										<core:Item key="Children" text="Old people"/>
									</MultiComboBox>
									<Label text=" VAT is included"/>
									<CheckBox selected="{/productVAT}"/>
								</form:SimpleForm>
							</WizardStep>
						</Wizard>
					</content>
					<footer>
						<OverflowToolbar>
							<ToolbarSpacer/>
							<Button text="Cancel" press="handleWizardCancel"/>
						</OverflowToolbar>
					</footer>
				</Page>
			</pages>
		</NavContainer>
</mvc:View>
<?php 	
}

function tplController(){
?>
sap.ui.define([
	'jquery.sap.global',
	'sap/ui/core/mvc/Controller',
	'sap/ui/model/json/JSONModel',
	"sap/m/MessageToast",
	"sap/m/MessageBox"
], function(jQuery, Controller, JSONModel, MessageToast, MessageBox) {
	"use strict";
 
	var WizardController = Controller.extend("sap.m.sample.Wizard.C", {
		onInit: function () {
			this._wizard = this.getView().byId("CreateProductWizard");
			this._oNavContainer = this.getView().byId("wizardNavContainer");
			this._oWizardContentPage = this.getView().byId("wizardContentPage");
			this._oWizardReviewPage = null;//sap.ui.xmlfragment("sap.m.sample.Wizard.ReviewPage", this);
 
			//this._oNavContainer.addPage(this._oWizardReviewPage);
			this.model = new sap.ui.model.json.JSONModel();
			this.model.setData({
				productNameState:"Error",
				productWeightState:"Error"
			});
			this.getView().setModel(this.model);
			this.model.setProperty("/productType", "Mobile");
			this.model.setProperty("/navApiEnabled", true);
			this.model.setProperty("/productVAT", false);
			this._setEmptyValue("/productManufacturer");
			this._setEmptyValue("/productDescription");
			this._setEmptyValue("/productPrice");
		},
		setProductType: function (evt) {
			var productType = evt.getSource().getTitle();
			this.model.setProperty("/productType", productType);
			this.getView().byId("ProductStepChosenType").setText("Chosen product type: " + productType);
			this._wizard.validateStep(this.getView().byId("ProductTypeStep"));
		},
		setProductTypeFromSegmented: function (evt) {
			var productType = evt.mParameters.button.getText();
			this.model.setProperty("/productType", productType);
			this._wizard.validateStep(this.getView().byId("ProductTypeStep"));
		},
		additionalInfoValidation : function () {
			var name = this.getView().byId("ProductName").getValue();
			var weight = parseInt(this.getView().byId("ProductWeight").getValue());
 
			isNaN(weight) ? this.model.setProperty("/productWeightState", "Error") : this.model.setProperty("/productWeightState", "None");
			name.length<6 ?  this.model.setProperty("/productNameState", "Error") : this.model.setProperty("/productNameState", "None");
 
			if (name.length < 6 || isNaN(weight))
				this._wizard.invalidateStep(this.getView().byId("ProductInfoStep"));
			else
				this._wizard.validateStep(this.getView().byId("ProductInfoStep"));
		},
		optionalStepActivation: function () {
			MessageToast.show(
				'This event is fired on activate of Step3.'
			);
		},
		optionalStepCompletion: function () {
			MessageToast.show(
				'This event is fired on complete of Step3. You can use it to gather the information, and lock the input data.'
			);
		},
		pricingActivate: function () {
			this.model.setProperty("/navApiEnabled", true);
		},
		pricingComplete: function () {
			this.model.setProperty("/navApiEnabled", false);
		},
		scrollFrom4to2 : function () {
			this._wizard.goToStep(this.getView().byId("ProductInfoStep"));
		},
		goFrom4to3 : function () {
			if (this._wizard.getProgressStep() === this.getView().byId("PricingStep"))
				this._wizard.previousStep();
		},
		goFrom4to5 : function () {
			if (this._wizard.getProgressStep() === this.getView().byId("PricingStep"))
				this._wizard.nextStep();
		},
		wizardCompletedHandler : function () {
			this._oNavContainer.to(this._oWizardReviewPage);
		},
		backToWizardContent : function () {
			this._oNavContainer.backToPage(this._oWizardContentPage.getId());
		},
		editStepOne : function () {
			this._handleNavigationToStep(0);
		},
		editStepTwo : function () {
			this._handleNavigationToStep(1);
		},
		editStepThree : function () {
			this._handleNavigationToStep(2);
		},
		editStepFour : function () {
			this._handleNavigationToStep(3);
		},
		_handleNavigationToStep : function (iStepNumber) {
			var that = this;
			function fnAfterNavigate () {
				that._wizard.goToStep(that._wizard.getSteps()[iStepNumber]);
				that._oNavContainer.detachAfterNavigate(fnAfterNavigate);
			}
 
			this._oNavContainer.attachAfterNavigate(fnAfterNavigate);
			this.backToWizardContent();
		},
		_handleMessageBoxOpen : function (sMessage, sMessageBoxType) {
			var that = this;
			MessageBox[sMessageBoxType](sMessage, {
				actions: [MessageBox.Action.YES, MessageBox.Action.NO],
				onClose: function (oAction) {
					if (oAction === MessageBox.Action.YES) {
						that._handleNavigationToStep(0);
						that._wizard.discardProgress(that._wizard.getSteps()[0]);
					}
				},
			});
		},
		_setEmptyValue : function (sPath) {
			this.model.setProperty(sPath, "n/a");
		},
		handleWizardCancel : function () {
			this._handleMessageBoxOpen("Are you sure you want to cancel your report?", "warning");
		},
		handleWizardSubmit : function () {
			this._handleMessageBoxOpen("Are you sure you want to submit your report?", "confirm");
		},
		productWeighStateFormatter: function (val) {
			return isNaN(val) ? "Error" : "None";
		},
		discardProgress: function () {
			this._wizard.discardProgress(this.getView().byId("ProductTypeStep"));
 
			var clearContent = function (content) {
				for (var i = 0; i < content.length ; i++) {
					if (content[i].setValue) {
						content[i].setValue("");
					}
 
					if (content[i].getContent) {
						clearContent(content[i].getContent());
					}
				}
			};
 
			this.model.setProperty("/productWeightState", "Error");
			this.model.setProperty("/productNameState", "Error")
			clearContent(this._wizard.getSteps());
		}
	});
 
	return WizardController;
})
<?php 	
}

// E::uiO(array(
// 	'name'=>'ouXmlView',
// 	'def'=>array(
// 		'content'=> evEscJs(evRunWithBuff('tplView',array())['output']),
// 		'controller'=>evRunWithBuff('tplController',array())['output'],
// 		'placeAt'=>'ouiContent',	
// 	),	
// ));
?>


      <!-- script>
         sap.ui.getCore().attachInit(function () {
            new sap.ui.unified.FileUploader({
               name : "file"
            }).placeAt("ouiContent");
         });
      </script-->

	 


<h3>Import danych z arkusza Excel (*.xls)</h3>



<form name="excelImportForm" id="excelImportForm" action="<?php echo E::routeHref('excel/import');?>?krok=import" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">


<table class="searchTable" id="importHide3" width="740">
<tbody>
<tr>
<td class="tdVertical">
<label id="fileLabel" for="file">Plik:</label>
</td>
<td class="tdData">
<input id="file" name="file" style="width: 260px;" type="file">
<div id="ouiOuter" style="width:100%;">
	<div id="ouiContent" style="width:90%;margin: 0 auto;">
	
	</div>
</div>
</td>
</tr>
<tr>
<td class="tdVertical">
<span id="nextSpan">
<input class="button" value="Powrót" onclick="document.location.href='<?php echo E::ctHref('settings/administration');?>';" type="button">
<input class="button" value="Dalej" type="submit">
</span>
<span id="uploadingSpan" style="display:none;">
Ładowanie pliku, proszę czekać...<br>
<img src="<?php echo SITE_URL;?>images/loading.gif">
</span>
</td>

</tr>
</tbody></table>
</form>