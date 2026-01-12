/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 07/01/25, 5:49 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

import { ref } from "vue";
import { defineStore } from "pinia";
// @ts-ignore
import ApiService from "@/core/services/ApiService";
// @ts-ignore
import JwtService from "@/core/services/JwtService";
// @ts-ignore
import { useOptionStore } from "@common@/components/optionStore";
// @ts-ignore
import { useAbilityStore } from "./ability";
// @ts-ignore
import { $headMeta } from "@/core/helpers/utility";
// @ts-ignore
import { _ } from "lodash";

export interface User {
    name: string;
    surname: string;
    email: string;
    password: string;
    api_token: string;
    settings: object;
}

export const useAuthStore = defineStore("auth", () => {
    const errors = ref({});
    const user = ref<User>({} as User);
    const isAuthenticated = ref(!!JwtService.getToken());

    //Last verification time
    const lastVerifiedAt = ref(0);
    //Verification duration every 20 seconds
    const verificationDuration = parseInt(
        $headMeta("verify_auth_timeout", "20000")
    );
    const verifying = ref(false);

    const optionStore = useOptionStore();
    const abilityStore = useAbilityStore();

    function setAuth(authUser: User) {
        isAuthenticated.value = true;
        user.value = authUser;
        errors.value = {};
        JwtService.saveToken(authUser.api_token);
        ApiService.setHeader();
    }

    function setError(error: any) {
        errors.value = { ...error };
    }

    function purgeAuth() {
        isAuthenticated.value = false;
        user.value = {} as User;
        errors.value = [];
        JwtService.destroyToken();
    }

    function login(credentials: User) {
        return ApiService.post("login", credentials)
            .then(({ data }) => {
                setAuth(data.data.user);
            })
            .catch(({ response }) => {
                setError([response.data.data]);
            });
    }

    function logout() {
        purgeAuth();
    }

    function register(credentials: User) {
        return ApiService.post("register", credentials)
            .then(async ({ data }) => {
                setAuth(data);
            })
            .catch(({ response }) => {
                setError(response.data.errors);
            });
    }

    function forgotPassword(email: string) {
        return ApiService.post("forgot_password", email)
            .then(() => {
                setError({});
            })
            .catch(({ response }) => {
                setError(response.data.errors || [response.data.message]);
            });
    }

    function resetPassword(credentials: any) {
        return ApiService.post("reset_password", credentials)
            .then(() => {
                setError({});
            })
            .catch(({ response }) => {
                setError(response.data.errors || [response.data.message]);
            });
    }

    function verifyAuth() {
        return new Promise((resolve) => {
            //Check if verification is needed
            updateLastVerifiedAt();
            if (lastVerifiedAt.value + verificationDuration > Date.now()) {
                resolve(true);
                return;
            }

            if (JwtService.getToken()) {
                ApiService.setHeader();
                verifying.value = true;
                ApiService.get("verify")
                    .then(async ({ data }) => {
                        setAuth(data.data.user);
                        abilityStore.setAbilities(data.data.abilities);
                        await optionStore.preloadOptions();
                        setLastVerifiedAt();
                        //set auto verify every verificationDuration seconds also make sure that setInterval is not duplicated also check if window is inactivated then clear interval and set new interval
                        //@ts-ignore
                        if (typeof window.verifyAuthInterval !== "undefined") {
                            //@ts-ignore
                            clearInterval(window.verifyAuthInterval);
                        }
                        //@ts-ignore
                        window.verifyAuthInterval = setInterval(() => {
                            verifyAuth();
                        }, verificationDuration);
                        resolve(data);
                    })
                    .catch(({ response }) => {
                        setError(response.data.errors);
                        purgeAuth();
                        resolve(response);
                    })
                    .finally(() => {
                        verifying.value = false;
                    });
            } else {
                purgeAuth();
                resolve(false);
            }
        });
    }

    const setLastVerifiedAt = (value = null) => {
        lastVerifiedAt.value = value === null ? Date.now() : value;
        localStorage.setItem(
            "last_verified_at",
            lastVerifiedAt.value.toString()
        );
    };

    const updateLastVerifiedAt = () => {
        lastVerifiedAt.value = parseInt(
            localStorage.getItem("last_verified_at") || "0"
        );
        //check if window is inactive
        if (document.hidden || verifying.value) {
            setLastVerifiedAt();
        }
        //check if user.value is empty object then set lastVerifiedAt to 0
        if (Object.keys(user.value).length === 0) {
            setLastVerifiedAt(0);
        }
    };

    const getUserSetting = (key: string, defaultValue: any = null) => {
        return _.get(user.value, `settings.${key}`, defaultValue);
    };

    const getUserSettings = () => {
        return _.get(user.value, `settings`, {});
    };

    const updateUserSetting = (key: string, value: any) => {
        return new Promise(async (resolve) => {
            user.value.settings = _.set(user.value, `settings.${key}`, value);
            await ApiService.post("user/setting/update", { key, value });
            resolve(true);
        });
    };

    const restrictCompanyChange = ref(false);

    const setCompanyChangeRestriction = (value: boolean) => {
        restrictCompanyChange.value = value;
    };

    return {
        errors,
        user,
        isAuthenticated,
        login,
        logout,
        register,
        forgotPassword,
        resetPassword,
        verifyAuth,
        getUserSetting,
        getUserSettings,
        updateUserSetting,
        restrictCompanyChange,
        setCompanyChangeRestriction,
    };
});
