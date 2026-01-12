/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 10/12/24, 6:49 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

import { useToast } from "vue-toast-notification";
import Swal from "sweetalert2";

const $toast = useToast();

const $appVersion = () => {
    return import.meta.env.PACKAGE_VERSION;
};

const $getUrl = (path) => {
    const version = $appVersion();
    return path + "?v=" + version;
};
const $catchResponse = (error) => {
    let message = error.message;
    if ($objIsset(error, "response", "data")) {
        message = error.response.data;
    }
    if ($objIsset(error, "response", "data", "message")) {
        message = error.response.data.message;
    }
    $toastError(message);
};

const $objIsset = (obj, ...props) => {
    return props.reduce(
        (acc, prop) => acc && obj.hasOwnProperty(prop) && (obj = obj[prop]),
        true
    );
};

const $headMeta = (key, falseValue = null) => {
    const v = document.head.querySelector('meta[name="' + key + '"]');
    return v ? v.content : falseValue;
};

const $setHeadMeta = (key, value) => {
    const v = document.head.querySelector('meta[name="' + key + '"]');
    if (v) {
        v.content = value;
    }
};

const stringifyObject = (obj) => {
    if (typeof obj === "object" && obj !== null) {
        for (let key in obj) {
            if (Array.isArray(obj[key])) {
                obj[key] = JSON.stringify(obj[key]);
            } else if (typeof obj[key] === "object") {
                obj[key] = stringifyObject(obj[key]);
            }
        }
    }
    return obj;
};

const $toastSuccess = (message) => {
    toast({ message: message, type: "success" });
};
const $toastWarning = (message) => {
    toast({ message: message, type: "warning" });
};
const $toastError = (message) => {
    toast({ message: message, type: "error" });
};
const $toastInfo = (message) => {
    toast({ message: message, type: "info" });
};
const toast = (params) => {
    const message = params.message;
    const options = { type: params.type };
    const defaultOptions = {
        duration: 4000,
        position: "top", //top, bottom, top-right, bottom-right,top-left, bottom-left
        queue: true,
    };
    $toast.open({
        message: message,
        ...defaultOptions,
        ...options,
    });
};

const $confirmDelete = (
    confirmButtonText = "Delete",
    cancelButtonText = "Cancel",
    title = "Are you sure?",
    message = "You won't be able to revert this!"
) => {
    return new Promise((resolve) => {
        Swal.fire({
            title: title,
            text: message,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            confirmButtonColor: "#dd4b39",
            cancelButtonText: cancelButtonText,
            focusCancel: true,
            reverseButtons: true,
        }).then((r) => {
            resolve(r.isConfirmed);
        });
    });
};

const $currencySymbol = (obj, subObj = "currency") => {
    const c = _.get(obj, subObj);
    return c ? c.symbol : "";
};

const $getCurrencyObj = (obj, subObj = "currency") => {
    const c = _.get(obj, subObj);
    return c ? c : "";
};

const $getValue = (
    obj,
    subObj,
    rValue = null,
    prefix = null,
    suffix = null
) => {
    const c = _.get(obj, subObj);
    const hasValue = c !== undefined && c !== null && c !== "";
    let value = c;
    if (hasValue && prefix) {
        value = prefix + value;
    }
    if (hasValue && suffix) {
        value = value + suffix;
    }
    return hasValue ? value : rValue;
};

const $getDisplayValue = (
    obj,
    subObj,
    rValue = "-",
    prefix = null,
    suffix = null
) => {
    return $getValue(obj, subObj, rValue, prefix, suffix);
};

const $getArrayDisplayValue = (obj, key, field, rValue = "-") => {
    const c = _.get(obj, key);
    if (!c || c.length === 0) return rValue;
    const fieldValues = c.map((item) => item[field]);
    return fieldValues.join(", ");
};

/* const $moneyfy = (amount, currency, locale = "en-US", rValue = 0) => {
    const currencyCode = currency ? currency.code : null;

    // Check if the provided currency code is valid
    if (!amount || !currencyCode) {
        return rValue;
    }
    try {
        Intl.NumberFormat(locale, {
            style: "currency",
            currency: currencyCode,
        }).format(0);
    } catch (error) {
        console.error(`Invalid currency code: ${currencyCode}`);
        return "";
    }

    // Format the currency using the provided locale and currency code
    const formatter = new Intl.NumberFormat(locale, {
        style: "currency",
        currency: currencyCode,
    });

    return formatter.format(amount);
}; */
const $moneyfy = (amount, code = "USD", locale = "en-US", rValue = 0) => {
    try {
        Intl.NumberFormat(locale, {
            style: "currency",
            currency: code,
        }).format(0);
    } catch (error) {
        console.error(`Invalid currency code: ${code}`);
        return "";
    }

    // Format the currency using the provided locale and currency code
    const formatter = new Intl.NumberFormat(locale, {
        style: "currency",
        currency: code,
    });

    return formatter.format(amount);
};

export {
    $appVersion,
    $getUrl,
    $catchResponse,
    $objIsset,
    $toastSuccess,
    $toastWarning,
    $toastError,
    $toastInfo,
    $headMeta,
    $setHeadMeta,
    $getCurrencyObj,
    $getValue,
    $getDisplayValue,
    $getArrayDisplayValue,
    $moneyfy,
    $confirmDelete,
};
