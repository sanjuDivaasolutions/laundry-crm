// @ts-ignore
import { mainMenuPages } from "@common@/data/mainmenu";
export interface MenuItem {
    heading?: string;
    sectionTitle?: string;
    route?: string;
    gate?: string;
    pages?: Array<MenuItem>;
    svgIcon?: string;
    fontIcon?: string;
    icon?: string;
    sub?: Array<MenuItem>;
}

const MainMenuConfig: Array<MenuItem> = mainMenuPages;

export default MainMenuConfig;
