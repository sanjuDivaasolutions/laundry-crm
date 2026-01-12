import FormLabel from "@common@/components/form/FormLabel.vue";
import FormItems from "@common@/components/form/FormItems.vue";
import FormSubItems from "@common@/components/form/FormSubItems.vue";
import FormInvoiceItems from "@common@/components/form/FormInvoiceItems.vue";
import FormGroup from "@common@/components/form/FormGroup.vue";
import FormGroupButton from "@common@/components/form/FormGroupButton.vue";
import FormCheckboxGroup from "@common@/components/form/FormCheckboxGroup.vue";
import FormCheckbox from "@common@/components/form/FormCheckbox.vue";
import FormCheckboxInline from "@common@/components/form/FormCheckboxInline.vue";
import FormAmountLabel from "@common@/components/form/FormAmountLabel.vue";
import FormButton from "@common@/components/form/FormButton.vue";
import FormIcon from "@common@/components/form/FormIcon.vue";
import FormInput from "@common@/components/form/FormInput.vue";
import FormDecimal from "@common@/components/form/FormDecimal.vue";
import FormSwitch from "@common@/components/form/FormSwitch.vue";
import FormTextarea from "@common@/components/form/FormTextarea.vue";
import FormCkEditor from "@common@/components/form/FormCkEditor.vue";
import FormDatepicker from "@common@/components/form/FormDatepicker.vue";
import FormDateRangePicker from "@common@/components/form/FormDateRangePicker.vue";
import FormSelectSingle from "@common@/components/form/FormSelectSingle.vue";
import FormSelectCurrency from "@common@/components/form/FormSelectCurrency.vue";
import FormSelectMultiple from "@common@/components/form/FormSelectMultiple.vue";
import FormSelectAjax from "@common@/components/form/FormSelectAjax.vue";
import FormFileSingle from "@common@/components/form/FormFileSingle.vue";
import FormFileDrop from "@common@/components/form/FormFileDrop.vue";

import CardContainer from "@common@/components/CardContainer.vue";
import OverviewHeader from "@common@/components/OverviewHeader.vue";

import TranslatedHeader from "@/components/magic-datatable/components/TranslatedHeader.vue";
import DatatableBadgeList from "@/components/magic-datatable/components/DatatableBadgeList.vue";
import DatatableList from "@/components/magic-datatable/components/DatatableList.vue";
import DatatableBadge from "@/components/magic-datatable/components/DatatableBadge.vue";
import DatatableLink from "@/components/magic-datatable/components/DatatableLink.vue";
import DatatableSoLink from "@/components/magic-datatable/components/DatatableSoLink.vue";
import DatatableLedgerLink from "@/components/magic-datatable/components/DatatableLedgerLink.vue";
import DatatableActions from "@/components/magic-datatable/components/DatatableActions.vue";
import DatatableJournalItemList from "@/components/magic-datatable/components/DatatableJournalItemList.vue";
import DatatableHtml from "@/components/magic-datatable/components/DatatableHtml.vue";
import ProductSaleDetailsButton from "@modules@/reports/components/ProductSaleDetailsButton.vue";
import ProductInwardDetailsButton from "@modules@/reports/components/ProductInwardDetailsButton.vue";
import AgentCommissionDetailsButton from "@modules@/reports/components/AgentCommissionDetailsButton.vue";
import BarcodeScanner from "@common@/components/BarcodeScanner.vue";
import BarcodeField from "@modules@/products/components/BarcodeField.vue";
import BarcodeQuickAdd from "@common@/components/BarcodeQuickAdd.vue";
import PaymentStatusLink from "@modules@/salesInvoices/components/PaymentStatusLink.vue";

import { Ckeditor } from "@ckeditor/ckeditor5-vue";

export default function registerFormGlobalComponents(app) {
    app.component("FormLabel", FormLabel);
    app.component("FormItems", FormItems);
    app.component("FormSubItems", FormSubItems);
    app.component("FormInvoiceItems", FormInvoiceItems);
    app.component("FormCheckboxGroup", FormCheckboxGroup);
    app.component("FormCheckbox", FormCheckbox);
    app.component("FormCheckboxInline", FormCheckboxInline);
    app.component("FormGroup", FormGroup);
    app.component("FormAmountLabel", FormAmountLabel);
    app.component("FormButton", FormButton);
    app.component("FormGroupButton", FormGroupButton);
    app.component("FormIcon", FormIcon);
    app.component("FormInput", FormInput);
    app.component("FormDecimal", FormDecimal);
    app.component("FormFileSingle", FormFileSingle);
    app.component("FormSwitch", FormSwitch);
    app.component("FormTextarea", FormTextarea);
    app.component("FormCkEditor", FormCkEditor);
    app.component("FormDatepicker", FormDatepicker);
    app.component("FormDateRangePicker", FormDateRangePicker);
    app.component("FormSelectSingle", FormSelectSingle);
    app.component("FormSelectCurrency", FormSelectCurrency);
    app.component("FormSelectMultiple", FormSelectMultiple);
    app.component("FormSelectAjax", FormSelectAjax);
    app.component("FormFileDrop", FormFileDrop);

    app.component("CardContainer", CardContainer);
    app.component("OverviewHeader", OverviewHeader);

    app.component("TranslatedHeader", TranslatedHeader);
    app.component("DatatableLink", DatatableLink);
    app.component("DatatableSoLink", DatatableSoLink);
    app.component("DatatableLedgerLink", DatatableLedgerLink);
    app.component("DatatableList", DatatableList);
    app.component("DatatableBadge", DatatableBadge);
    app.component("DatatableBadgeList", DatatableBadgeList);
    app.component("DatatableJournalItemList", DatatableJournalItemList);
    app.component("DatatableActions", DatatableActions);
    app.component("DatatableHtml", DatatableHtml);
    app.component("ProductSaleDetailsButton", ProductSaleDetailsButton);
    app.component("ProductInwardDetailsButton", ProductInwardDetailsButton);
    app.component("AgentCommissionDetailsButton", AgentCommissionDetailsButton);
    app.component("PaymentStatusLink", PaymentStatusLink);
    app.component("BarcodeScanner", BarcodeScanner);
    app.component("BarcodeField", BarcodeField);
    app.component("BarcodeQuickAdd", BarcodeQuickAdd);

    app.component("ckeditor", Ckeditor.component);
}
