// @ts-ignore
import i18n from "@/core/plugins/i18n";
const $t = (text: string) => {
    if (i18n.global.te(text)) {
        return i18n.global.t(text);
    } else {
        /*if (text.includes(".fields.")) {
            const splitText = text.split(".fields.");
            text = splitText[0] + ".general." + splitText[1];
        }
        return i18n.global.te(text) ? i18n.global.t(text) : text;*/
        return text;
    }
};

export { $t };
