import { ref } from "vue";
import { defineStore } from "pinia";
// @ts-ignore
import ApiService from "@/core/services/ApiService";
import { PureAbility } from "@casl/ability";

export const useAbilityStore = defineStore("ability", () => {
    const abilities = new PureAbility();
    const fetchAbilities = () => {
        return ApiService.get("abilities").then((res) => {
            setAbilities(res.data.data.abilities);
        });
    };

    const setAbilities = (data: any, subject = "all") => {
        abilities.update([{ subject: "all", action: data }]);
    };

    return {
        abilities,
        fetchAbilities,
        setAbilities,
    };
});
