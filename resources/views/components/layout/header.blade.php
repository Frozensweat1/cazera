  @php
      $settings = \App\Support\WebsiteContent::settings();
      $brandName = $settings?->business_name ?: config('app.name', 'Cazera');
      $brandLogo = $settings?->logo ? \App\Support\WebsiteContent::assetPath($settings->logo) : null;
      $brandInitials = collect(explode(' ', $brandName))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
      $brandInitials = $brandInitials ?: mb_strtoupper(mb_substr($brandName, 0, 2));
  @endphp

  <header class="z-40" :class="{ 'dark': $store.app.semidark && $store.app.menu === 'horizontal' }">
      <div class="shadow-sm">
          <div class="relative flex w-full items-center bg-white px-5 py-2.5 dark:bg-[#0e1726]">
              <div class="horizontal-logo flex items-center justify-between ltr:mr-2 rtl:ml-2 lg:hidden">
                  <a href="{{ route('dashboard') }}" class="main-logo flex shrink-0 items-center">
                      <span class="inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-xl bg-primary/10 text-xs font-bold text-primary ltr:-ml-1 rtl:-mr-1 dark:bg-white/10 dark:text-white">
                          @if ($brandLogo)
                              <img class="h-full w-full object-contain p-1.5" src="{{ $brandLogo }}" alt="{{ $brandName }} logo">
                          @else
                              {{ $brandInitials }}
                          @endif
                      </span>
                      <span
                          class="hidden max-w-[12rem] truncate align-middle text-xl font-semibold transition-all duration-300 ltr:ml-2 rtl:mr-2 dark:text-white-light md:inline">{{ $brandName }}</span>
                  </a>

                  <a href="javascript:;"
                      class="collapse-icon flex flex-none rounded-full bg-white-light/40 p-2 hover:bg-white-light/90 hover:text-primary ltr:ml-2 rtl:mr-2 dark:bg-dark/40 dark:text-[#d0d2d6] dark:hover:bg-dark/60 dark:hover:text-primary lg:hidden"
                      @click="$store.app.toggleSidebar()">
                      <svg width="20" height="20" viewbox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <path d="M20 7L4 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                          </path>
                          <path opacity="0.5" d="M20 12L4 12" stroke="currentColor" stroke-width="1.5"
                              stroke-linecap="round"></path>
                          <path d="M20 17L4 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                      </svg>
                  </a>
              </div>




                  <div x-data="header"

                  class="flex items-center space-x-1.5 ltr:ml-auto rtl:mr-auto rtl:space-x-reverse dark:text-[#d0d2d6] sm:flex-1 ltr:sm:ml-0 sm:rtl:mr-0 lg:space-x-2">
                  <div class="sm:ltr:mr-auto sm:rtl:ml-auto">
                      <form action="{{ route('backoffice.branch.select') }}" method="POST" class="relative max-w-xs">
                          @csrf
                          <label for="header-branch-selector" class="sr-only">Select branch</label>
                          <select id="header-branch-selector" name="branch_id" onchange="this.form.submit()"
                              class="form-input w-full rounded-full border border-gray-200 bg-gray-100 py-2 pl-4 pr-10 text-sm text-gray-700 outline-none transition duration-150 ease-in-out focus:border-primary focus:bg-white dark:border-[#273449] dark:bg-[#0d1b2e] dark:text-white">
                              @if ($branches->isEmpty())
                                  <option value="">No active branches</option>
                              @else
                                  @foreach ($branches as $branch)
                                      <option value="{{ $branch->id }}" @selected($branch->id == $currentBranch?->id)>
                                          {{ $branch->name }}
                                      </option>
                                  @endforeach
                              @endif
                          </select>
                      </form>
                  </div>
                  <div>
                      <a href="javascript:;" x-cloak="" x-show="$store.app.theme === 'light'" href="javascript:;"
                          class="flex items-center rounded-full bg-white-light/40 p-2 hover:bg-white-light/90 hover:text-primary dark:bg-dark/40 dark:hover:bg-dark/60"
                          @click="$store.app.toggleTheme('dark')">
                          <svg width="20" height="20" viewbox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="1.5">
                              </circle>
                              <path d="M12 2V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path d="M12 20V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path d="M4 12L2 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path d="M22 12L20 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path opacity="0.5" d="M19.7778 4.22266L17.5558 6.25424" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path opacity="0.5" d="M4.22217 4.22266L6.44418 6.25424" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path opacity="0.5" d="M6.44434 17.5557L4.22211 19.7779" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path opacity="0.5" d="M19.7778 19.7773L17.5558 17.5551" stroke="currentColor"
                                  stroke-width="1.5" stroke-linecap="round">
                              </path>
                          </svg>
                      </a>
                      <a href="javascript:;" x-cloak="" x-show="$store.app.theme === 'dark'" href="javascript:;"
                          class="flex items-center rounded-full bg-white-light/40 p-2 hover:bg-white-light/90 hover:text-primary dark:bg-dark/40 dark:hover:bg-dark/60"
                          @click="$store.app.toggleTheme('system')">
                          <svg width="20" height="20" viewbox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                  d="M21.0672 11.8568L20.4253 11.469L21.0672 11.8568ZM12.1432 2.93276L11.7553 2.29085V2.29085L12.1432 2.93276ZM21.25 12C21.25 17.1086 17.1086 21.25 12 21.25V22.75C17.9371 22.75 22.75 17.9371 22.75 12H21.25ZM12 21.25C6.89137 21.25 2.75 17.1086 2.75 12H1.25C1.25 17.9371 6.06294 22.75 12 22.75V21.25ZM2.75 12C2.75 6.89137 6.89137 2.75 12 2.75V1.25C6.06294 1.25 1.25 6.06294 1.25 12H2.75ZM15.5 14.25C12.3244 14.25 9.75 11.6756 9.75 8.5H8.25C8.25 12.5041 11.4959 15.75 15.5 15.75V14.25ZM20.4253 11.469C19.4172 13.1373 17.5882 14.25 15.5 14.25V15.75C18.1349 15.75 20.4407 14.3439 21.7092 12.2447L20.4253 11.469ZM9.75 8.5C9.75 6.41182 10.8627 4.5828 12.531 3.57467L11.7553 2.29085C9.65609 3.5593 8.25 5.86509 8.25 8.5H9.75ZM12 2.75C11.9115 2.75 11.8077 2.71008 11.7324 2.63168C11.6686 2.56527 11.6538 2.50244 11.6503 2.47703C11.6461 2.44587 11.6482 2.35557 11.7553 2.29085L12.531 3.57467C13.0342 3.27065 13.196 2.71398 13.1368 2.27627C13.0754 1.82126 12.7166 1.25 12 1.25V2.75ZM21.7092 12.2447C21.6444 12.3518 21.5541 12.3539 21.523 12.3497C21.4976 12.3462 21.4347 12.3314 21.3683 12.2676C21.2899 12.1923 21.25 12.0885 21.25 12H22.75C22.75 11.2834 22.1787 10.9246 21.7237 10.8632C21.286 10.804 20.7293 10.9658 20.4253 11.469L21.7092 12.2447Z"
                                  fill="currentColor"></path>
                          </svg>
                      </a>
                      <a href="javascript:;" x-cloak="" x-show="$store.app.theme === 'system'"
                          href="javascript:;"
                          class="flex items-center rounded-full bg-white-light/40 p-2 hover:bg-white-light/90 hover:text-primary dark:bg-dark/40 dark:hover:bg-dark/60"
                          @click="$store.app.toggleTheme('light')">
                          <svg width="20" height="20" viewbox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                  d="M3 9C3 6.17157 3 4.75736 3.87868 3.87868C4.75736 3 6.17157 3 9 3H15C17.8284 3 19.2426 3 20.1213 3.87868C21 4.75736 21 6.17157 21 9V14C21 15.8856 21 16.8284 20.4142 17.4142C19.8284 18 18.8856 18 17 18H7C5.11438 18 4.17157 18 3.58579 17.4142C3 16.8284 3 15.8856 3 14V9Z"
                                  stroke="currentColor" stroke-width="1.5"></path>
                              <path opacity="0.5" d="M22 21H2" stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round"></path>
                              <path opacity="0.5" d="M15 15H9" stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round"></path>
                          </svg>
                      </a>
                  </div>



                  @if (auth()->user()?->isSuperAdmin())
                  <div class="dropdown" x-data="dropdown" @click.outside="open = false">
                      <a href="javascript:;"
                          class="block rounded-full bg-white-light/40 p-2 hover:bg-white-light/90 hover:text-primary dark:bg-dark/40 dark:hover:bg-dark/60"
                          @click="toggle">
                          <svg width="20" height="20" viewbox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                  d="M22 10C22.0185 10.7271 22 11.0542 22 12C22 15.7712 22 17.6569 20.8284 18.8284C19.6569 20 17.7712 20 14 20H10C6.22876 20 4.34315 20 3.17157 18.8284C2 17.6569 2 15.7712 2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4H13"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path
                                  d="M6 8L8.1589 9.79908C9.99553 11.3296 10.9139 12.0949 12 12.0949C13.0861 12.0949 14.0045 11.3296 15.8411 9.79908"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <circle cx="19" cy="5" r="3" stroke="currentColor" stroke-width="1.5">
                              </circle>
                          </svg>
                      </a>
                      <ul x-cloak="" x-show="open" x-transition="" x-transition.duration.300ms=""
                          class="top-11 w-[320px] !py-0 text-xs text-dark ltr:-right-16 rtl:-left-16 dark:text-white-dark sm:w-[375px] sm:ltr:-right-2 sm:rtl:-left-2">


                          <li class="mb-5">
                              <div class="relative overflow-hidden rounded-t-md !p-5 text-white">
                                  <div
                                      class="absolute inset-0 h-full w-full bg-[url('../images/menu-heade.jpg')] bg-cover bg-center bg-no-repeat">
                                  </div>
                                  <h4 class="relative z-10 text-lg font-semibold">Messages</h4>
                              </div>
                          </li>
                          <template x-for="msg in messages" :key="msg.id">
                              <li>
                                  <div class="flex items-center px-5 py-3" @click.self="toggle">
                                      <span class="px-3 dark:text-gray-500">
                                          <div class="text-sm font-semibold dark:text-white-light/90" x-text="msg.name"></div>
                                          <div class="text-xs text-gray-600 dark:text-gray-300 truncate" x-text="msg.subject"></div>
                                          <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="msg.message"></div>
                                      </span>
                                      <span
                                          class="whitespace-pre rounded bg-white-dark/20 px-1 font-semibold text-dark/60 ltr:ml-auto ltr:mr-2 rtl:ml-2 rtl:mr-auto dark:text-white-dark"
                                          x-text="msg.time"></span>
                                  </div>
                              </li>
                          </template>

                          <template x-if="messages.length">
                              <li class="mt-5 border-t border-white-light text-center dark:border-white/10">
                                  <a href="{{ route('backoffice.website.contact-messages') }}" class="group flex cursor-pointer items-center justify-center px-4 py-4 font-semibold text-primary dark:text-gray-400"
                                      @click="toggle">
                                      <span class="group-hover:underline ltr:mr-1 rtl:ml-1">VIEW ALL
                                          MESSAGES</span>
                                      <svg class="h-4 w-4 transition duration-300 group-hover:translate-x-1 ltr:ml-1 rtl:mr-1"
                                          viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                          <path d="M4 12H20M20 12L14 6M20 12L14 18" stroke="currentColor"
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                          </path>
                                      </svg>
                                  </a>
                              </li>
                          </template>
                          <template x-if="!messages.length">
                              <li class="mb-5">
                                  <div class="!grid min-h-[200px] place-content-center text-lg hover:!bg-transparent">
                                      <div class="mx-auto mb-4 rounded-full text-primary ring-4 ring-primary/30">
                                          <svg width="40" height="40" viewbox="0 0 20 20" fill="none"
                                              xmlns="http://www.w3.org/2000/svg">
                                              <path opacity="0.5"
                                                  d="M20 10C20 4.47715 15.5228 0 10 0C4.47715 0 0 4.47715 0 10C0 15.5228 4.47715 20 10 20C15.5228 20 20 15.5228 20 10Z"
                                                  fill="currentColor"></path>
                                              <path
                                                  d="M10 4.25C10.4142 4.25 10.75 4.58579 10.75 5V11C10.75 11.4142 10.4142 11.75 10 11.75C9.58579 11.75 9.25 11.4142 9.25 11V5C9.25 4.58579 9.58579 4.25 10 4.25Z"
                                                  fill="currentColor"></path>
                                              <path
                                                  d="M10 15C10.5523 15 11 14.5523 11 14C11 13.4477 10.5523 13 10 13C9.44772 13 9 13.4477 9 14C9 14.5523 9.44772 15 10 15Z"
                                                  fill="currentColor"></path>
                                          </svg>
                                      </div>
                                      No data available.
                                  </div>
                              </li>
                          </template>
                      </ul>
                  </div>
                  <div class="dropdown" x-data="dropdown" @click.outside="open = false">
                      <a href="javascript:;"
                          class="relative block rounded-full bg-white-light/40 p-2 hover:bg-white-light/90 hover:text-primary dark:bg-dark/40 dark:hover:bg-dark/60"
                          @click="toggle">
                          <svg width="20" height="20" viewbox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                  d="M19.0001 9.7041V9C19.0001 5.13401 15.8661 2 12.0001 2C8.13407 2 5.00006 5.13401 5.00006 9V9.7041C5.00006 10.5491 4.74995 11.3752 4.28123 12.0783L3.13263 13.8012C2.08349 15.3749 2.88442 17.5139 4.70913 18.0116C9.48258 19.3134 14.5175 19.3134 19.291 18.0116C21.1157 17.5139 21.9166 15.3749 20.8675 13.8012L19.7189 12.0783C19.2502 11.3752 19.0001 10.5491 19.0001 9.7041Z"
                                  stroke="currentColor" stroke-width="1.5"></path>
                              <path d="M7.5 19C8.15503 20.7478 9.92246 22 12 22C14.0775 22 15.845 20.7478 16.5 19"
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                              <path d="M12 6V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                              </path>
                          </svg>

                          <span class="absolute top-0 flex h-3 w-3 ltr:right-0 rtl:left-0">
                              <span
                                  class="absolute -top-[3px] inline-flex h-full w-full animate-ping rounded-full bg-success/50 opacity-75 ltr:-left-[3px] rtl:-right-[3px]"></span>
                              <span class="relative inline-flex h-[6px] w-[6px] rounded-full bg-success"></span>
                          </span>
                      </a>
                      <ul x-cloak="" x-show="open" x-transition="" x-transition.duration.300ms=""
                          class="top-11 w-[300px] divide-y !py-0 text-dark ltr:-right-2 rtl:-left-2 dark:divide-white/10 dark:text-white-dark sm:w-[350px]">
                          <li>
                              <div
                                  class="flex items-center justify-between px-4 py-2 font-semibold hover:!bg-transparent">
                                  <h4 class="text-lg">Recent Logs</h4>
                                  <template x-if="notifications.length">
                                      <span class="badge bg-primary/80" x-text="notifications.length + ' New'"></span>
                                  </template>
                              </div>
                          </li>
                          <li x-show="notifications.length" class="max-h-[24rem] overflow-y-auto overscroll-contain">
                              <template x-for="notification in notifications" :key="notification.id">
                                  <div class="dark:text-white-light/90">
                                      <div class="group flex items-center px-4 py-2" @click.self="toggle">
                                          <div class="grid h-11 w-11 shrink-0 place-content-center rounded-xl"
                                              :class="notification.tone === 'success' ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary'">
                                              <span class="text-xs font-bold" x-text="notification.type"></span>
                                          </div>
                                          <div class="flex flex-auto ltr:pl-3 rtl:pr-3">
                                              <div class="ltr:pr-3 rtl:pl-3">
                                                  <h6 class="font-semibold" x-text="notification.title"></h6>
                                                  <p class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400" x-text="notification.message"></p>
                                                  <p class="text-xs text-gray-400" x-text="notification.actor"></p>
                                                  <span class="block text-xs font-normal dark:text-gray-500"
                                                      x-text="notification.time"></span>
                                              </div>
                                              <button type="button"
                                                  class="text-neutral-300 opacity-0 hover:text-danger group-hover:opacity-100 ltr:ml-auto rtl:mr-auto"
                                                  @click="removeNotification(notification.id)">
                                                  <svg width="20" height="20" viewbox="0 0 24 24" fill="none"
                                                      xmlns="http://www.w3.org/2000/svg">
                                                      <circle opacity="0.5" cx="12" cy="12" r="10"
                                                          stroke="currentColor" stroke-width="1.5">
                                                      </circle>
                                                      <path d="M14.5 9.50002L9.5 14.5M9.49998 9.5L14.5 14.5"
                                                          stroke="currentColor" stroke-width="1.5"
                                                          stroke-linecap="round"></path>
                                                  </svg>
                                              </button>
                                          </div>
                                      </div>
                                  </div>
                              </template>
                          </li>
                          <template x-if="notifications.length">
                              <li>
                                  <div class="grid gap-2 p-4 sm:grid-cols-2">
                                      <a href="{{ route('backoffice.logs.activity') }}" class="btn btn-primary btn-small block w-full text-center" @click="toggle">Activity Logs</a>
                                      <a href="{{ route('backoffice.logs.audit') }}" class="btn btn-secondary btn-small block w-full text-center" @click="toggle">Audit Logs</a>
                                  </div>
                              </li>
                          </template>
                          <template x-if="!notifications.length">
                              <li>
                                  <div class="!grid min-h-[200px] place-content-center text-lg hover:!bg-transparent">
                                      <div class="mx-auto mb-4 rounded-full text-primary ring-4 ring-primary/30">
                                          <svg width="40" height="40" viewbox="0 0 20 20" fill="none"
                                              xmlns="http://www.w3.org/2000/svg">
                                              <path opacity="0.5"
                                                  d="M20 10C20 4.47715 15.5228 0 10 0C4.47715 0 0 4.47715 0 10C0 15.5228 4.47715 20 10 20C15.5228 20 20 15.5228 20 10Z"
                                                  fill="currentColor"></path>
                                              <path
                                                  d="M10 4.25C10.4142 4.25 10.75 4.58579 10.75 5V11C10.75 11.4142 10.4142 11.75 10 11.75C9.58579 11.75 9.25 11.4142 9.25 11V5C9.25 4.58579 9.58579 4.25 10 4.25Z"
                                                  fill="currentColor"></path>
                                              <path
                                                  d="M10 15C10.5523 15 11 14.5523 11 14C11 13.4477 10.5523 13 10 13C9.44772 13 9 13.4477 9 14C9 14.5523 9.44772 15 10 15Z"
                                                  fill="currentColor"></path>
                                          </svg>
                                      </div>
                                      No data available.
                                  </div>
                              </li>
                          </template>
                      </ul>
                  </div>
                  @endif
                  <div class="dropdown flex-shrink-0" x-data="dropdown" @click.outside="open = false">
                      @php
                          $avatarUrl = null;
                          if (auth()->check()) {
                              $avatarUrl = auth()->user()->profile_img ?? auth()->user()->profile_image ?? auth()->user()->avatar ?? null;
                          }
                          $avatarUrl = $avatarUrl ? asset('storage/' . $avatarUrl) : null;
                          $name = auth()->user()->name ?? auth()->user()->email ?? '';
                          $email = auth()->user()->email ?? '';
                          $initials = trim(collect(explode(' ', $name))->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode(''));
                          $initials = $initials ?: mb_strtoupper(mb_substr($email, 0, 2));
                      @endphp
                      <a href="javascript:;" class="group relative" @click="toggle()">
                          <span class="inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-gray-200 text-sm font-bold text-gray-700 saturate-50 group-hover:saturate-100">
                              @if($avatarUrl)
                                  <img class="h-full w-full object-cover" src="{{ $avatarUrl }}" alt="User avatar" />
                              @else
                                  {{ $initials }}
                              @endif
                          </span>
                      </a>
                      <ul x-cloak="" x-show="open" x-transition="" x-transition.duration.300ms=""
                          class="top-11 w-[230px] !py-0 font-semibold text-dark ltr:right-0 rtl:left-0 dark:text-white-dark dark:text-white-light/90">
                          <li>
                              <div class="flex items-center px-4 py-4">
                                  <div class="flex-none">
                                      @php
                                          $name = auth()->user()->name ?? auth()->user()->email ?? '';
                                          $email = auth()->user()->email ?? '';
                                          $initials = trim(collect(explode(' ', $name))->filter()->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode(''));
                                          $initials = $initials ?: mb_strtoupper(mb_substr($email, 0, 2));
                                          $avatarUrl = (auth()->check() && (auth()->user()->profile_img ?? auth()->user()->profile_image ?? auth()->user()->avatar ?? null))
                                              ? asset('storage/' . (auth()->user()->profile_img ?? auth()->user()->profile_image ?? auth()->user()->avatar))
                                              : null;
                                      @endphp

                                      <div class="flex-none">
                                          @if($avatarUrl)
                                              <img class="h-10 w-10 rounded-md object-cover" src="{{ $avatarUrl }}" alt="User avatar">
                                          @else
                                              <div class="h-10 w-10 rounded-md bg-gray-200 text-sm font-bold text-gray-700 inline-flex items-center justify-center">
                                                  {{ $initials }}
                                              </div>
                                          @endif
                                      </div>
                                  </div>
                                  <div class="truncate ltr:pl-4 rtl:pr-4">
                                      <h4 class="text-base">{{ $name }}
                                      </h4>
                                      <a class="text-black/60 hover:text-primary dark:text-dark-light/60 dark:hover:text-white" href="javascript:;">{{ $email }}</a>
                                  </div>
                              </div>
                          </li>
                          <li>
                              <a href="{{ route('backoffice.profile') }}" class="dark:hover:text-white" @click="toggle()">
                                  <svg class="h-4.5 w-4.5 shrink-0 ltr:mr-2 rtl:ml-2" width="18" height="18"

                                      viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                      <circle cx="12" cy="6" r="4" stroke="currentColor"
                                          stroke-width="1.5"></circle>
                                      <path opacity="0.5"
                                          d="M20 17.5C20 19.9853 20 22 12 22C4 22 4 19.9853 4 17.5C4 15.0147 7.58172 13 12 13C16.4183 13 20 15.0147 20 17.5Z"
                                          stroke="currentColor" stroke-width="1.5"></path>
                                  </svg>
                                  Profile</a>
                          </li>
                          <li>
                              <a href="javascript:;" class="dark:hover:text-white" @click="toggle(); window.location='{{ route('backoffice.profile') }}'">

                                  <svg class="h-4.5 w-4.5 shrink-0 ltr:mr-2 rtl:ml-2" width="18" height="18"
                                      viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                      <path
                                          d="M2 16C2 13.1716 2 11.7574 2.87868 10.8787C3.75736 10 5.17157 10 8 10H16C18.8284 10 20.2426 10 21.1213 10.8787C22 11.7574 22 13.1716 22 16C22 18.8284 22 20.2426 21.1213 21.1213C20.2426 22 18.8284 22 16 22H8C5.17157 22 3.75736 22 2.87868 21.1213C2 20.2426 2 18.8284 2 16Z"
                                          stroke="currentColor" stroke-width="1.5"></path>
                                      <path opacity="0.5"
                                          d="M6 10V8C6 4.68629 8.68629 2 12 2C15.3137 2 18 4.68629 18 8V10"
                                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                      </path>
                                      <g opacity="0.5">
                                          <path
                                              d="M9 16C9 16.5523 8.55228 17 8 17C7.44772 17 7 16.5523 7 16C7 15.4477 7.44772 15 8 15C8.55228 15 9 15.4477 9 16Z"
                                              fill="currentColor"></path>
                                          <path
                                              d="M13 16C13 16.5523 12.5523 17 12 17C11.4477 17 11 16.5523 11 16C11 15.4477 11.4477 15 12 15C12.5523 15 13 15.4477 13 16Z"
                                              fill="currentColor"></path>
                                          <path
                                              d="M17 16C17 16.5523 16.5523 17 16 17C15.4477 17 15 16.5523 15 16C15 15.4477 15.4477 15 16 15C16.5523 15 17 15.4477 17 16Z"
                                              fill="currentColor"></path>
                                      </g>
                                  </svg>
                                  Lock Screen</a>
                          </li>
                          <li class="border-t border-white-light dark:border-white-light/10">
                              <form method="POST" action="{{ route('logout') }}" class="!py-3">
                                  @csrf
                                  <button type="submit" class="flex w-full items-center !py-3 text-danger" @click="toggle">
                                      <svg class="h-4.5 w-4.5 shrink-0 rotate-90 ltr:mr-2 rtl:ml-2" width="18"
                                          height="18" viewbox="0 0 24 24" fill="none"
                                          xmlns="http://www.w3.org/2000/svg">
                                          <path opacity="0.5"
                                              d="M17 9.00195C19.175 9.01406 20.3529 9.11051 21.1213 9.8789C22 10.7576 22 12.1718 22 15.0002V16.0002C22 18.8286 22 20.2429 21.1213 21.1215C20.2426 22.0002 18.8284 22.0002 16 22.0002H8C5.17157 22.0002 3.75736 22.0002 2.87868 21.1215C2 20.2429 2 18.8286 2 16.0002L2 15.0002C2 12.1718 2 10.7576 2.87868 9.87889C3.64706 9.11051 4.82497 9.01406 7 9.00195"
                                              stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                          </path>
                                          <path d="M12 15L12 2M12 2L15 5.5M12 2L9 5.5" stroke="currentColor"
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                      </svg>
                                      Sign Out
                                  </button>
                              </form>
                          </li>

                      </ul>
                  </div>
              </div>
          </div>

          <!-- horizontal menu -->

      </div>
  </header>
